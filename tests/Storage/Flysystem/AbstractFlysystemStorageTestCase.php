<?php

namespace Vich\UploaderBundle\Tests\Storage\Flysystem;

use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use League\Flysystem\UnableToDeleteFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Symfony\Component\ErrorHandler\Error\UndefinedMethodError;
use Vich\UploaderBundle\Storage\FlysystemStorage;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\Storage\StorageTestCase;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
abstract class AbstractFlysystemStorageTestCase extends StorageTestCase
{
    final public const FS_KEY = 'filesystemKey';

    protected MountManager|ContainerInterface $registry;

    protected Filesystem|MockObject $filesystem;

    protected FilesystemAdapter|MockObject $adapter;

    protected bool $useFlysystemToResolveUri = false;

    abstract protected function createRegistry(FilesystemOperator $filesystem): MountManager|ContainerInterface;

    protected function getStorage(): StorageInterface
    {
        return new FlysystemStorage($this->factory, $this->registry, $this->useFlysystemToResolveUri);
    }

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->adapter = $this->createMock(FilesystemAdapter::class);
        $this->registry = $this->createRegistry($this->filesystem);

        parent::setUp();

        $this->mapping
            ->method('getUploadDestination')
            ->willReturn(self::FS_KEY);
    }

    public function testUpload(): void
    {
        $file = $this->getUploadedFileMock();

        $file
            ->expects(self::once())
            ->method('getRealPath')
            ->willReturn($this->root->url().\DIRECTORY_SEPARATOR.'uploads'.\DIRECTORY_SEPARATOR.'test.txt');

        $file
            ->expects(self::once())
            ->method('getClientOriginalName')
            ->willReturn('originalName.txt');

        $this->mapping
            ->expects(self::once())
            ->method('getFile')
            ->willReturn($file);

        $this->mapping
            ->expects(self::once())
            ->method('getUploadName')
            ->with($this->object)
            ->willReturn('originalName.txt');

        $this->filesystem
            ->expects(self::once())
            ->method('writeStream')
            ->with(
                'originalName.txt',
                $this->isResource(),
                $this->isArray()
            );

        $this->storage->upload($this->object, $this->mapping);
    }

    public function testRemove(): void
    {
        $this->filesystem
            ->expects(self::once())
            ->method('delete')
            ->with('test.txt');

        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->willReturn('test.txt');

        $this->storage->remove($this->object, $this->mapping);
    }

    public function testRemoveOnNonExistentFile(): void
    {
        $this->filesystem
            ->expects(self::once())
            ->method('delete')
            ->with('not_found.txt')
            ->will($this->throwException(new UnableToDeleteFile('dummy path')));

        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->willReturn('not_found.txt');

        $this->expectException(UnableToDeleteFile::class);
        $this->expectExceptionMessage('dummy path');
        $this->storage->remove($this->object, $this->mapping);
    }

    #[DataProvider('pathProvider')]
    public function testResolvePath(?string $uploadDir, string $expectedPath, bool $relative): void
    {
        $this->mapping
            ->expects(self::once())
            ->method('getUploadDir')
            ->willReturn($uploadDir);

        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects(self::once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $path = $this->storage->resolvePath($this->object, 'file_field', null, $relative);

        self::assertEquals($expectedPath, $path);
    }

    public static function pathProvider(): array
    {
        return [
            //     dir,   path,                     relative
            [null,  'file.txt',               true],
            [null,  'file.txt',               false],
            ['foo', 'foo/file.txt',           true],
            ['foo', 'foo/file.txt',           false],
            ['0',   '0/file.txt',             true],
            ['0',   '0/file.txt',             false],
        ];
    }

    public function testResolveUri(): void
    {
        $this->mapping
            ->expects(self::once())
            ->method('getUriPrefix')
            ->willReturn('/uploads');

        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects(self::once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $path = $this->getStorage()->resolveUri($this->object, 'file_field');

        self::assertEquals('/uploads/file.txt', $path);
    }

    public function testResolveUriWithZeroDirectory(): void
    {
        $this->mapping
            ->expects(self::once())
            ->method('getUriPrefix')
            ->willReturn('/uploads');

        $this->mapping
            ->expects(self::once())
            ->method('getUploadDir')
            ->willReturn('0');

        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects(self::once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $path = $this->getStorage()->resolveUri($this->object, 'file_field');

        self::assertEquals('/uploads/0/file.txt', $path);
    }

    #[RequiresMethod(Filesystem::class, 'publicUrl')]
    public function testResolveUriThroughFlysystem(): void
    {
        $this->useFlysystemToResolveUri = true;

        $this->filesystem
            ->expects(self::once())
            ->method('publicUrl')
            ->with('file.txt', [
                'object' => $this->object,
                'fieldName' => 'file_field',
                'className' => null,
                'mapping' => $this->mapping,
            ])
            ->willReturn('example.com/file.txt');

        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects(self::exactly(2))
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $path = $this->getStorage()->resolveUri($this->object, 'file_field');

        self::assertEquals('example.com/file.txt', $path);
    }

    #[RequiresMethod(Filesystem::class, 'publicUrl')]
    public function testResolveUriThroughFlysystemWithZeroDirectory(): void
    {
        $this->useFlysystemToResolveUri = true;

        $this->filesystem
            ->expects(self::once())
            ->method('publicUrl')
            ->with('0/file.txt', [
                'object' => $this->object,
                'fieldName' => 'file_field',
                'className' => null,
                'mapping' => $this->mapping,
            ])
            ->willReturn('example.com/0/file.txt');

        $this->mapping
            ->expects(self::once())
            ->method('getUploadDir')
            ->willReturn('0');

        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects(self::exactly(2))
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $path = $this->getStorage()->resolveUri($this->object, 'file_field');

        self::assertEquals('example.com/0/file.txt', $path);
    }

    #[RequiresMethod(Filesystem::class, 'publicUrl')]
    public function testResolveUriHandlesUndefinedMethodError(): void
    {
        $this->useFlysystemToResolveUri = true;

        $this->filesystem
            ->expects(self::once())
            ->method('publicUrl')
            ->with('file.txt')
            ->will($this->throwException(new UndefinedMethodError('Undefined method', new \Error('An error occurred'))));

        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->mapping
            ->expects(self::once())
            ->method('getUriPrefix')
            ->willReturn('/uploads');

        $this->factory
            ->expects(self::exactly(2))
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $path = $this->getStorage()->resolveUri($this->object, 'file_field');

        self::assertEquals('/uploads/file.txt', $path);
    }

    public function testListFiles(): void
    {
        // Create FileAttributes with timestamps (2 hours old to pass min-age filter)
        $timestamp = \time() - 7200;
        $file1 = new FileAttributes('file1.txt', null, null, $timestamp);
        $file2 = new FileAttributes('file2.txt', null, null, $timestamp);
        $file3 = new FileAttributes('subdir/file3.txt', null, null, $timestamp);

        $this->filesystem
            ->expects(self::once())
            ->method('listContents')
            ->with('/', true)
            ->willReturn(new DirectoryListing(new \ArrayIterator([$file1, $file2, $file3])));

        $files = \iterator_to_array($this->storage->listFiles($this->mapping));

        self::assertCount(3, $files);

        // Extract paths from StoredFile objects
        $paths = \array_map(fn ($file) => $file->path, $files);
        self::assertContains('file1.txt', $paths);
        self::assertContains('file2.txt', $paths);
        self::assertContains('subdir/file3.txt', $paths);

        // Verify that all files have timestamps
        foreach ($files as $file) {
            self::assertNotNull($file->lastModifiedAt);
            self::assertIsInt($file->lastModifiedAt);
            self::assertEquals($timestamp, $file->lastModifiedAt);
        }
    }

    public function testListFilesWithEmptyListing(): void
    {
        $this->filesystem
            ->expects(self::once())
            ->method('listContents')
            ->with('/', true)
            ->willReturn(new DirectoryListing(new \ArrayIterator([])));

        $files = \iterator_to_array($this->storage->listFiles($this->mapping));

        self::assertCount(0, $files);
    }

    public function testListFilesWithFilesystemException(): void
    {
        $this->filesystem
            ->expects(self::once())
            ->method('listContents')
            ->with('/', true)
            ->will($this->throwException($this->createMock(FilesystemException::class)));

        $files = \iterator_to_array($this->storage->listFiles($this->mapping));

        self::assertCount(0, $files);
    }

    public function testListFilesWithNullTimestamps(): void
    {
        // Create FileAttributes without timestamps
        $file1 = new FileAttributes('file1.txt', null, null, null);
        $file2 = new FileAttributes('file2.txt', null, null, null);

        $this->filesystem
            ->expects(self::once())
            ->method('listContents')
            ->with('/', true)
            ->willReturn(new DirectoryListing(new \ArrayIterator([$file1, $file2])));

        $files = \iterator_to_array($this->storage->listFiles($this->mapping));

        self::assertCount(2, $files);

        // Verify that files have null timestamps
        foreach ($files as $file) {
            self::assertNull($file->lastModifiedAt);
        }
    }

    public function testListFilesSkipsDirectories(): void
    {
        $timestamp = \time() - 7200;
        $file1 = new FileAttributes('file1.txt', null, null, $timestamp);

        // Create a DirectoryAttributes mock
        $dir = $this->createMock(\League\Flysystem\DirectoryAttributes::class);
        $dir->method('isFile')->willReturn(false);
        $dir->method('path')->willReturn('subdir');

        $file2 = new FileAttributes('file2.txt', null, null, $timestamp);

        $this->filesystem
            ->expects(self::once())
            ->method('listContents')
            ->with('/', true)
            ->willReturn(new DirectoryListing(new \ArrayIterator([$file1, $dir, $file2])));

        $files = \iterator_to_array($this->storage->listFiles($this->mapping));

        // Should only have 2 files, directory should be skipped
        self::assertCount(2, $files);

        $paths = \array_map(fn ($file) => $file->path, $files);
        self::assertContains('file1.txt', $paths);
        self::assertContains('file2.txt', $paths);
        self::assertNotContains('subdir', $paths);
    }
}
