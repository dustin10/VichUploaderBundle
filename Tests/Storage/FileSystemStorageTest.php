<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Vich\UploaderBundle\Storage\FileSystemStorage;

/**
 * FileSystemStorageTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class FileSystemStorageTest extends StorageTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getStorage()
    {
        return new FileSystemStorage($this->factory);
    }

    /**
     * Tests the upload method skips a mapping which has a non
     * uploadable property value.
     *
     * @expectedException   \LogicException
     * @dataProvider        invalidFileProvider
     * @group               upload
     */
    public function testUploadSkipsMappingOnInvalid($file)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));

        $this->mapping
            ->expects($this->never())
            ->method('hasNamer');

        $this->mapping
            ->expects($this->never())
            ->method('getNamer');

        $this->mapping
            ->expects($this->never())
            ->method('getFileName');

        $this->storage->upload($this->object, $this->mapping);
    }

    /**
     * Test the remove method skips trying to remove a file whose file name
     * property value returns null.
     *
     * @dataProvider emptyFilenameProvider
     */
    public function testRemoveSkipsEmptyFilenameProperties($propertyValue)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue($propertyValue));

        $this->mapping
            ->expects($this->never())
            ->method('getUploadDir');

        $this->storage->remove($this->object, $this->mapping);
    }

    /**
     * Test the remove method skips trying to remove a file that no longer exists.
     */
    public function testRemoveSkipsNonExistingFile()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue($this->getValidUploadDir()));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('foo.txt'));

        $this->storage->remove($this->object, $this->mapping);
    }

    public function testRemove()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue($this->getValidUploadDir()));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('test.txt'));

        $this->storage->remove($this->object, $this->mapping);
        $this->assertFalse($this->root->hasChild('uploads'.DIRECTORY_SEPARATOR.'test.txt'));
    }

    /**
     * Test the resolve path method.
     */
    public function testResolvePath()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue(''));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue('/tmp'));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->will($this->returnValue($this->mapping));

        $path = $this->storage->resolvePath($this->object, 'file_field');

        $this->assertEquals(sprintf('/tmp%sfile.txt', DIRECTORY_SEPARATOR), $path);
    }

    /**
     * Test the resolve path method.
     */
    public function testResolveRelativePath()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue('upload_dir'));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->will($this->returnValue($this->mapping));

        $path = $this->storage->resolvePath($this->object, 'file_field', null, true);

        $this->assertEquals(sprintf('upload_dir%sfile.txt', DIRECTORY_SEPARATOR), $path);
    }

    public function testResolveUriReturnsNullIfNoFile()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue(null));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->will($this->returnValue($this->mapping));

        $this->assertNull($this->storage->resolveUri($this->object, 'file_field'));
    }

    /**
     * Test the resolve uri.
     *
     * @dataProvider resolveUriDataProvider
     */
    public function testResolveUri($uploadDir, $uri)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue($uploadDir));

        $this->mapping
            ->expects($this->once())
            ->method('getUriPrefix')
            ->will($this->returnValue('/uploads'));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->will($this->returnValue($this->mapping));

        $path = $this->storage->resolveUri($this->object, 'file_field');

        $this->assertEquals($uri, $path);
    }

    public function testResolveStream()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue(''));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue($this->root->url().'/uploads'));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('test.txt'));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->will($this->returnValue($this->mapping));

        $stream = $this->storage->resolveStream($this->object, 'file_field', null);

        $this->assertNotEmpty($stream);
    }

    public function resolveUriDataProvider()
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
        ];
    }

    /**
     * @dataProvider filenameWithDirectoriesDataProvider
     * @group upload
     */
    public function testUploadedFileIsCorrectlyMoved($uploadDir, $dir, $expectedDir)
    {
        $file = $this->getUploadedFileMock();

        $file
            ->expects($this->any())
            ->method('getClientOriginalName')
            ->will($this->returnValue('filename.txt'));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($file));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue($uploadDir));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadName')
            ->with($this->object)
            ->will($this->returnValue('filename.txt'));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->with($this->object)
            ->will($this->returnValue($dir));

        $file
            ->expects($this->once())
            ->method('move')
            ->with($expectedDir, 'filename.txt');

        $this->storage->upload($this->object, $this->mapping);
    }

    public function filenameWithDirectoriesDataProvider()
    {
        return [
            // upload dir, dir, expected dir
            [
                '/root_dir',
                '',
                '/root_dir/',
            ],
            [
                '/root_dir',
                'dir_1',
                '/root_dir/dir_1',
            ],
            [
                '/root_dir',
                'dir_1/dir_2',
                '/root_dir/dir_1/dir_2',
            ],
        ];
    }
}
