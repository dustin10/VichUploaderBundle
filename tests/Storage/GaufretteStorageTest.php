<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Gaufrette\Adapter;
use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\Exception\FileNotFound;
use Gaufrette\Filesystem;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Vich\UploaderBundle\Storage\GaufretteStorage;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 */
class GaufretteStorageTest extends StorageTestCase
{
    protected FilesystemMap|MockObject $filesystemMap;

    protected function getStorage(): StorageInterface
    {
        return new GaufretteStorage($this->factory, $this->filesystemMap);
    }

    protected function setUp(): void
    {
        if (!\class_exists(FilesystemMap::class)) {
            self::markTestSkipped('GaufretteBundle is not installed.');
        }
        $this->filesystemMap = $this->createMock(FilesystemMap::class);

        parent::setUp();
    }

    /**
     * Test the remove method skips trying to remove a file whose file name
     * property value returns null.
     */
    public function testRemoveSkipsNullFileNameProperty(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn(null);

        $this->mapping
            ->expects($this->never())
            ->method('getUploadDir');

        $this->storage->remove($this->object, $this->mapping);
    }

    #[DataProvider('pathProvider')]
    public function testResolvePath(string $protocol, string $filesystemKey, ?string $uploadDir, string $expectedPath, bool $relative): void
    {
        $this->mapping
            ->method('getUploadDestination')
            ->willReturn($filesystemKey);

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->willReturn($uploadDir);

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $this->storage = new GaufretteStorage($this->factory, $this->filesystemMap, $protocol);
        $path = $this->storage->resolvePath($this->object, 'file_field', null, $relative);

        self::assertEquals($expectedPath, $path);
    }

    public function testResolveUri(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUriPrefix')
            ->willReturn('/uploads');

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $this->storage = new GaufretteStorage($this->factory, $this->filesystemMap, 'gaufrette');
        $path = $this->storage->resolveUri($this->object, 'file_field');

        self::assertEquals('/uploads/file.txt', $path);
    }

    public function testResolveUriFileNull(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('');

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $this->storage = new GaufretteStorage($this->factory, $this->filesystemMap, 'gaufrette');
        $path = $this->storage->resolveUri($this->object, 'file_field');

        self::assertNull($path);
    }

    public function testResolveUriWithZeroDirectory(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUriPrefix')
            ->willReturn('/uploads');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->willReturn('0');

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $this->storage = new GaufretteStorage($this->factory, $this->filesystemMap, 'gaufrette');
        $path = $this->storage->resolveUri($this->object, 'file_field');

        self::assertEquals('/uploads/0/file.txt', $path);
    }

    public static function pathProvider(): array
    {
        return [
            // protocol, fs identifier, upload dir, full path, relative
            ['gaufrette', 'filesystemKey', null,   'gaufrette://filesystemKey/file.txt', false],
            ['data',      'filesystemKey', null,   'data://filesystemKey/file.txt', false],
            ['gaufrette', 'filesystemKey', 'foo',  'gaufrette://filesystemKey/foo/file.txt', false],
            ['gaufrette', 'filesystemKey', '0',    'gaufrette://filesystemKey/0/file.txt', false],
            ['gaufrette', 'filesystemKey', null,   'file.txt', true],
            ['gaufrette', 'filesystemKey', 'foo',  'foo/file.txt', true],
            ['gaufrette', 'filesystemKey', '0',    '0/file.txt', true],
        ];
    }

    /**
     * Test the remove method does delete file from gaufrette filesystem.
     */
    public function testThatRemoveMethodDoesDeleteFile(): void
    {
        $this->mapping
            ->method('getUploadDestination')
            ->willReturn('filesystemKey');
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $filesystem = $this->getFilesystemMock();
        $filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('file.txt')
            ->willReturn(true);

        $this
            ->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->willReturn($filesystem);

        $this->storage->remove($this->object, $this->mapping);
    }

    /**
     * Test that FileNotFound exception is caught.
     */
    public function testRemoveNotFoundFile(): void
    {
        // the exception is caught in the UploadHandler.
        $this->expectException(FileNotFound::class);
        $this->mapping
            ->method('getUploadDestination')
            ->willReturn('filesystemKey');
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $filesystem = $this->getFilesystemMock();
        $filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('file.txt')
            ->will($this->throwException(new FileNotFound('File Not Found')));

        $this
            ->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->willReturn($filesystem);

        $this->storage->remove($this->object, $this->mapping);
    }

    public function testUploadSetsMetadataWhenUsingMetadataSupporterAdapter(): void
    {
        $filesystem = $this->getFilesystemMock();
        $file = $this->getUploadedFileMock();
        $adapter = $this->createMock(MetadataSupporter::class);

        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn('filename');

        $file
            ->expects($this->once())
            ->method('getPathname')
            ->willReturn($this->getValidUploadDir().\DIRECTORY_SEPARATOR.'test.txt');

        // Ensure mime type is used in metadata payload
        $file
            ->method('getMimeType')
            ->willReturn('text/plain');

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->willReturn($file);

        $this->mapping
            ->expects($this->once())
            ->method('getUploadName')
            ->with($this->object)
            ->willReturn('filename');

        // Explicitly return empty uploadDir (root)
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->willReturn('');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn('filesystemKey');

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->willReturn($filesystem);

        // Assert exact parameters for metadata: path and contentType
        $adapter
            ->expects($this->once())
            ->method('setMetadata')
            ->with('filename', ['contentType' => 'text/plain']);

        $filesystem
            ->method('getAdapter')
            ->willReturn($adapter);

        $filesystem
            ->expects($this->once())
            ->method('write')
            ->with('filename', 'some content');

        $this->storage->upload($this->object, $this->mapping);
    }

    public function testUploadSetsMetadataWhenUsingMetadataSupporterAdapterWithUploadDir(): void
    {
        $filesystem = $this->getFilesystemMock();
        $file = $this->getUploadedFileMock();
        $adapter = $this->createMock(MetadataSupporter::class);

        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn('filename');

        $file
            ->expects($this->once())
            ->method('getPathname')
            ->willReturn($this->getValidUploadDir().\DIRECTORY_SEPARATOR.'test.txt');

        $file
            ->method('getMimeType')
            ->willReturn('text/plain');

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->willReturn($file);

        $this->mapping
            ->expects($this->once())
            ->method('getUploadName')
            ->with($this->object)
            ->willReturn('filename');

        // Non-empty uploadDir should prefix the path
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->willReturn('foo');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn('filesystemKey');

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->willReturn($filesystem);

        // setMetadata must receive the full path including directory and correct contentType
        $adapter
            ->expects($this->once())
            ->method('setMetadata')
            ->with('foo/filename', ['contentType' => 'text/plain']);

        $filesystem
            ->method('getAdapter')
            ->willReturn($adapter);

        $filesystem
            ->expects($this->once())
            ->method('write')
            ->with('foo/filename', 'some content');

        $this->storage->upload($this->object, $this->mapping);
    }

    public function testUploadDoesNotSetMetadataWhenUsingNonMetadataSupporterAdapter(): void
    {
        $adapter = $this->createMock(Adapter::class);
        $filesystem = $this->getFilesystemMock();
        $file = $this->getUploadedFileMock();

        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn('filename');

        $file
            ->expects($this->once())
            ->method('getPathname')
            ->willReturn($this->getValidUploadDir().\DIRECTORY_SEPARATOR.'test.txt');

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->willReturn($file);

        $this->mapping
            ->expects($this->once())
            ->method('getUploadName')
            ->with($this->object)
            ->willReturn('filename');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->willReturn('');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn('filesystemKey');

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->willReturn($filesystem);

        $filesystem
            ->method('getAdapter')
            ->willReturn($adapter);

        $filesystem
            ->expects($this->once())
            ->method('write')
            ->with('filename', 'some content');

        $this->storage->upload($this->object, $this->mapping);
    }

    protected function getFilesystemMock(): Filesystem|MockObject
    {
        return $this->createMock(Filesystem::class);
    }

    public function testListFiles(): void
    {
        $filesystem = $this->getFilesystemMock();

        // Create timestamps (2 hours old to pass min-age filter)
        $timestamp = \time() - 7200;

        $filesystem
            ->expects($this->once())
            ->method('listKeys')
            ->willReturn([
                'keys' => ['file1.txt', 'file2.txt', 'subdir/file3.txt'],
                'dirs' => ['subdir'],
            ]);

        $filesystem
            ->method('has')
            ->willReturn(true);

        $filesystem
            ->method('mtime')
            ->willReturn($timestamp);

        $filesystem
            ->method('get')
            ->willReturn($this->createMock(\Gaufrette\File::class));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn('filesystemKey');

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->willReturn($filesystem);

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
        $filesystem = $this->getFilesystemMock();

        $filesystem
            ->expects($this->once())
            ->method('listKeys')
            ->willReturn(['keys' => [], 'dirs' => []]);

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn('filesystemKey');

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->willReturn($filesystem);

        $files = \iterator_to_array($this->storage->listFiles($this->mapping));

        self::assertCount(0, $files);
    }

    public function testListFilesWithException(): void
    {
        $filesystem = $this->getFilesystemMock();

        $filesystem
            ->expects($this->once())
            ->method('listKeys')
            ->will($this->throwException(new \RuntimeException('Filesystem error')));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn('filesystemKey');

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->willReturn($filesystem);

        $files = \iterator_to_array($this->storage->listFiles($this->mapping));

        self::assertCount(0, $files);
    }

    public function testListFilesWithNullTimestamps(): void
    {
        $filesystem = $this->getFilesystemMock();

        $filesystem
            ->expects($this->once())
            ->method('listKeys')
            ->willReturn(['keys' => ['file1.txt', 'file2.txt'], 'dirs' => []]);

        $filesystem
            ->method('has')
            ->willReturn(true);

        $filesystem
            ->method('mtime')
            ->willReturn(null);

        $filesystem
            ->method('get')
            ->willReturn($this->createMock(\Gaufrette\File::class));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn('filesystemKey');

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->willReturn($filesystem);

        $files = \iterator_to_array($this->storage->listFiles($this->mapping));

        self::assertCount(2, $files);

        // Verify that files have null timestamps
        foreach ($files as $file) {
            self::assertNull($file->lastModifiedAt);
        }
    }

    public function testListFilesSkipsDirectories(): void
    {
        $filesystem = $this->getFilesystemMock();

        $timestamp = \time() - 7200;

        $filesystem
            ->expects($this->once())
            ->method('listKeys')
            ->willReturn([
                'keys' => ['file1.txt', 'subdir/', 'file2.txt'],
                'dirs' => ['subdir'],
            ]);

        $filesystem
            ->method('has')
            ->willReturn(true);

        $filesystem
            ->method('mtime')
            ->willReturn($timestamp);

        // Simulate directory detection: get() fails for trailing slash
        $filesystem
            ->method('get')
            ->willReturn($this->createMock(\Gaufrette\File::class));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn('filesystemKey');

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->willReturn($filesystem);

        $files = \iterator_to_array($this->storage->listFiles($this->mapping));

        // Should only have 2 files, directory should be skipped
        self::assertCount(2, $files);

        $paths = \array_map(fn ($file) => $file->path, $files);
        self::assertContains('file1.txt', $paths);
        self::assertContains('file2.txt', $paths);
        self::assertNotContains('subdir/', $paths);
    }
}
