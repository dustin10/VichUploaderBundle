<?php

namespace Vich\UploaderBundle\Tests\Storage;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Vich\UploaderBundle\Storage\FileSystemStorage;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class FileSystemStorageTest extends StorageTestCase
{
    protected function getStorage(): StorageInterface
    {
        return new FileSystemStorage($this->factory);
    }

    /**
     * Test the remove method skips trying to remove a file whose file name
     * property value returns null.
     */
    #[DataProvider('emptyFilenameProvider')]
    public function testRemoveSkipsEmptyFilenameProperties(?string $propertyValue): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn($propertyValue);

        $this->mapping
            ->expects(self::never())
            ->method('getUploadDir');

        $this->storage->remove($this->object, $this->mapping);
    }

    /**
     * Test the remove method skips trying to remove a file that no longer exists.
     */
    public function testRemoveSkipsNonExistingFile(): void
    {
        $this->expectException(\Exception::class);

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->willReturn($this->getValidUploadDir());

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('foo.txt');

        $this->storage->remove($this->object, $this->mapping);
    }

    public function testRemove(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn($this->getValidUploadDir());

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('test.txt');

        $this->storage->remove($this->object, $this->mapping);
        self::assertFalse($this->root->hasChild('uploads'.\DIRECTORY_SEPARATOR.'test.txt'));
    }

    /**
     * Test remove with explicit directory parameter (bypassing DirectoryNamer).
     * This is useful for cleanup operations where the entity no longer exists.
     */
    public function testRemoveWithExplicitDirectory(): void
    {
        // Create a file in a subdirectory
        $subdir = $this->getValidUploadDir().\DIRECTORY_SEPARATOR.'user_123';
        \mkdir($subdir, 0o777, true);
        \file_put_contents($subdir.\DIRECTORY_SEPARATOR.'avatar.jpg', 'content');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn($this->getValidUploadDir());

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('avatar.jpg');

        // getUploadDir should NOT be called when explicit $dir is provided
        $this->mapping
            ->expects(self::never())
            ->method('getUploadDir');

        // Pass directory explicitly, bypassing DirectoryNamer
        $this->storage->remove($this->object, $this->mapping, 'user_123');

        self::assertFalse($this->root->hasChild('uploads'.\DIRECTORY_SEPARATOR.'user_123'.\DIRECTORY_SEPARATOR.'avatar.jpg'));
    }

    /**
     * Test the resolve path method.
     */
    public function testResolvePath(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->willReturn('');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn('/tmp');

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $path = $this->storage->resolvePath($this->object, 'file_field');

        self::assertEquals(\sprintf('/tmp%sfile.txt', \DIRECTORY_SEPARATOR), $path);
    }

    /**
     * Test the resolve path method without passing field name.
     */
    public function testResolvePathWithoutFieldName(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->willReturn('');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn('/tmp');

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects($this->once())
            ->method('fromFirstField')
            ->with($this->object)
            ->willReturn($this->mapping);

        $path = $this->storage->resolvePath($this->object);

        self::assertEquals(\sprintf('/tmp%sfile.txt', \DIRECTORY_SEPARATOR), $path);
    }

    /**
     * Test the resolve path method.
     */
    public function testResolveRelativePath(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->willReturn('upload_dir');

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $path = $this->storage->resolvePath($this->object, 'file_field', null, true);

        self::assertEquals(\sprintf('upload_dir%sfile.txt', \DIRECTORY_SEPARATOR), $path);
    }

    public function testResolveUriReturnsNullIfNoFile(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn(null);

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        self::assertNull($this->storage->resolveUri($this->object, 'file_field'));
    }

    #[DataProvider('resolveUriDataProvider')]
    public function testResolveUri(string $uploadDir, string $uri): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->willReturn($uploadDir);

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

        $path = $this->storage->resolveUri($this->object, 'file_field');

        self::assertEquals($uri, $path);
    }

    public function testResolveStream(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->willReturn('');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn($this->root->url().'/uploads');

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('test.txt');

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $stream = $this->storage->resolveStream($this->object, 'file_field', null);

        self::assertNotEmpty($stream);
    }

    public static function resolveUriDataProvider(): array
    {
        return [
            [
                '',
                '/uploads/file.txt',
            ],
            [
                'dir',
                '/uploads/dir/file.txt',
            ],
            [
                'dir/sub-dir',
                '/uploads/dir/sub-dir/file.txt',
            ],
            [
                'dir\\sub-dir',
                '/uploads/dir/sub-dir/file.txt',
            ],
            [
                '0',
                '/uploads/0/file.txt',
            ],
        ];
    }

    #[DataProvider('filenameWithDirectoriesDataProvider')]
    #[Group('upload')]
    public function testUploadedFileIsCorrectlyMoved(string $uploadDir, string $dir, string $expectedDir): void
    {
        $uploadDir = $this->root->url().\DIRECTORY_SEPARATOR.$uploadDir;
        $expectedDir = $this->root->url().\DIRECTORY_SEPARATOR.$expectedDir;
        $file = $this->getUploadedFileMock();

        $file
            ->method('getClientOriginalName')
            ->willReturn('test.txt');

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn($file);

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn($uploadDir);

        $this->mapping
            ->expects($this->once())
            ->method('getUploadName')
            ->with($this->object)
            ->willReturn('test.txt');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->with($this->object)
            ->willReturn($dir);

        $file
            ->expects($this->once())
            ->method('move')
            ->with($expectedDir, 'test.txt');

        $this->storage->upload($this->object, $this->mapping);
    }

    #[Group('upload')]
    public function testReplacingFileIsCorrectlyUploaded(): void
    {
        $file = $this->getReplacingFileMock();
        $file
            ->method('getClientOriginalName')
            ->willReturn('test.txt');
        $file
            ->method('getPathname')
            ->willReturn($this->getValidUploadDir().'/test.txt');

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn($file);

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn($this->root->url().\DIRECTORY_SEPARATOR.'storage');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadName')
            ->with($this->object)
            ->willReturn('test.txt');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->with($this->object)
            ->willReturn('vich_uploader_bundle');

        $this->storage->upload($this->object, $this->mapping);
    }

    #[Group('upload')]
    public function testReplacingFileWithDirectoryNamerIsCorrectlyUploaded(): void
    {
        $file = $this->getReplacingFileMock();
        $file
            ->method('getClientOriginalName')
            ->willReturn('test.txt');
        $file
            ->method('getPathname')
            ->willReturn($this->getValidUploadDir().'/test.txt');

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn($file);

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn($this->root->url().\DIRECTORY_SEPARATOR.'storage');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadName')
            ->with($this->object)
            ->willReturn('test.txt');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->with($this->object)
            ->willReturn('vich_uploader_bundle/directoryNamer/1/');

        $this->storage->upload($this->object, $this->mapping);
    }

    public static function filenameWithDirectoriesDataProvider(): array
    {
        return [
            // upload dir, dir, expected dir
            'zero subdirectories' => [
                '/storage/vich_uploader_bundle',
                '',
                '/storage/vich_uploader_bundle/',
            ],
            'one subdirectory' => [
                '/storage/vich_uploader_bundle',
                'dir_1',
                '/storage/vich_uploader_bundle/dir_1',
            ],
            'two subdirectories' => [
                '/storage/vich_uploader_bundle',
                'dir_1/dir_2',
                '/storage/vich_uploader_bundle/dir_1/dir_2',
            ],
        ];
    }

    public function testListFiles(): void
    {
        // Create a new test directory separate from the default uploads
        $uploadDir = $this->root->url().'/test_list_files';
        \mkdir($uploadDir, 0o777, true);
        \mkdir($uploadDir.'/subdir', 0o777, true);
        \file_put_contents($uploadDir.'/file1.txt', 'content1');
        \file_put_contents($uploadDir.'/file2.txt', 'content2');
        \file_put_contents($uploadDir.'/subdir/file3.txt', 'content3');

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn($uploadDir);

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
        }
    }

    public function testListFilesWithEmptyDirectory(): void
    {
        // Create a new empty directory
        $uploadDir = $this->root->url().'/test_empty_dir';
        \mkdir($uploadDir, 0o777, true);

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn($uploadDir);

        $files = \iterator_to_array($this->storage->listFiles($this->mapping));

        self::assertCount(0, $files);
    }

    public function testListFilesWithNonExistentDirectory(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn($this->root->url().'/nonexistent');

        $files = \iterator_to_array($this->storage->listFiles($this->mapping));

        self::assertCount(0, $files);
    }
}
