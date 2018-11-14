<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Gaufrette\Exception\FileNotFound;
use Gaufrette\Filesystem;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Vich\UploaderBundle\Storage\GaufretteStorage;
use Vich\UploaderBundle\Storage\StorageInterface;
use Gaufrette\Adapter;
use Gaufrette\Adapter\MetadataSupporter;

/**
 * GaufretteStorageTest.
 *
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 */
class GaufretteStorageTest extends StorageTestCase
{
    /**
     * @var FilesystemMap
     */
    protected $filesystemMap;

    protected function getStorage(): StorageInterface
    {
        return new GaufretteStorage($this->factory, $this->filesystemMap);
    }

    protected function setUp(): void
    {
        $this->filesystemMap = $this->getFilesystemMapMock();

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
            ->will($this->returnValue(null));

        $this->mapping
            ->expects($this->never())
            ->method('getUploadDir');

        $this->storage->remove($this->object, $this->mapping);
    }

    /**
     * Test the resolve path method.
     *
     * @dataProvider pathProvider
     */
    public function testResolvePath($protocol, $filesystemKey, $uploadDir, $expectedPath, $relative): void
    {
        $this->mapping
            ->method('getUploadDestination')
            ->will($this->returnValue($filesystemKey));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue($uploadDir));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->will($this->returnValue($this->mapping));

        $this->storage = new GaufretteStorage($this->factory, $this->filesystemMap, $protocol);
        $path = $this->storage->resolvePath($this->object, 'file_field', null, $relative);

        $this->assertEquals($expectedPath, $path);
    }

    public function testResolveUri(): void
    {
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

        $this->storage = new GaufretteStorage($this->factory, $this->filesystemMap, 'gaufrette');
        $path = $this->storage->resolveUri($this->object, 'file_field');

        $this->assertEquals('/uploads/file.txt', $path);
    }

    public function testResolveUriFileNull(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue(''));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->will($this->returnValue($this->mapping));

        $this->storage = new GaufretteStorage($this->factory, $this->filesystemMap, 'gaufrette');
        $path = $this->storage->resolveUri($this->object, 'file_field');

        $this->assertNull($path);
    }

    public function pathProvider(): array
    {
        return [
            // protocol, fs identifier, upload dir, full path, relative
            ['gaufrette', 'filesystemKey', null,   'gaufrette://filesystemKey/file.txt', false],
            ['data',      'filesystemKey', null,   'data://filesystemKey/file.txt', false],
            ['gaufrette', 'filesystemKey', 'foo',  'gaufrette://filesystemKey/foo/file.txt', false],
            ['gaufrette', 'filesystemKey', null,   'file.txt', true],
            ['gaufrette', 'filesystemKey', 'foo',  'foo/file.txt', true],
        ];
    }

    /**
     * Test the remove method does delete file from gaufrette filesystem.
     */
    public function testThatRemoveMethodDoesDeleteFile(): void
    {
        $this->mapping
            ->expects($this->any())
            ->method('getUploadDestination')
            ->will($this->returnValue('filesystemKey'));
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

        $filesystem = $this->getFilesystemMock();
        $filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('file.txt');

        $this
            ->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->will($this->returnValue($filesystem));

        $this->storage->remove($this->object, $this->mapping);
    }

    /**
     * Test that FileNotFound exception is catched.
     */
    public function testRemoveNotFoundFile(): void
    {
        $this->mapping
            ->expects($this->any())
            ->method('getUploadDestination')
            ->will($this->returnValue('filesystemKey'));
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

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
            ->will($this->returnValue($filesystem));

        $this->storage->remove($this->object, $this->mapping);
    }

    public function testUploadSetsMetadataWhenUsingMetadataSupporterAdapter(): void
    {
        $filesystem = $this->getFilesystemMock();
        $file = $this->getUploadedFileMock();
        $adapter = $this->createMock( MetadataSupporter::class );

        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('filename'));

        $file
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->getValidUploadDir().\DIRECTORY_SEPARATOR.'test.txt'));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadName')
            ->with($this->object)
            ->will($this->returnValue('filename'));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue('filesystemKey'));

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->will($this->returnValue($filesystem));

        $adapter
            ->expects($this->once())
            ->method('setMetadata');

        $filesystem
            ->expects($this->any())
            ->method('getAdapter')
            ->will($this->returnValue($adapter));

        $filesystem
            ->expects($this->once())
            ->method('write')
            ->with('filename', 'some content');

        $this->storage->upload($this->object, $this->mapping);
    }

    public function testUploadDoesNotSetMetadataWhenUsingNonMetadataSupporterAdapter(): void
    {
        $adapter = $this->createMock( Adapter::class );
        $filesystem = $this->getFilesystemMock();
        $file = $this->getUploadedFileMock();

        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('filename'));

        $file
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->getValidUploadDir().\DIRECTORY_SEPARATOR.'test.txt'));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadName')
            ->with($this->object)
            ->will($this->returnValue('filename'));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue(''));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue('filesystemKey'));

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->will($this->returnValue($filesystem));

        $filesystem
            ->expects($this->any())
            ->method('getAdapter')
            ->will($this->returnValue($adapter));

        $filesystem
            ->expects($this->once())
            ->method('write')
            ->with('filename', 'some content');

        $this->storage->upload($this->object, $this->mapping);
    }

    protected function getFilesystemMapMock(): FilesystemMap
    {
        return $this
            ->getMockBuilder(FilesystemMap::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getFilesystemMock(): Filesystem
    {
        return $this
            ->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
