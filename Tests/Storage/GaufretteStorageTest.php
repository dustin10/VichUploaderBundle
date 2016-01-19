<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Gaufrette\Filesystem;
use Gaufrette\Exception\FileNotFound;
use Knp\Bundle\GaufretteBundle\FilesystemMap;

use Vich\UploaderBundle\Storage\GaufretteStorage;

/**
 * GaufretteStorageTest.
 *
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 */
class GaufretteStorageTest extends StorageTestCase
{
    /**
     * @var FilesystemMap $factory
     */
    protected $filesystemMap;

    /**
     * {@inheritDoc}
     */
    protected function getStorage()
    {
        return new GaufretteStorage($this->factory, $this->filesystemMap);
    }

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->filesystemMap = $this->getFilesystemMapMock();

        parent::setUp();
    }

    /**
     * Tests the upload method skips a mapping which has a non
     * uploadable property value.
     *
     * @expectedException   LogicException
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
     */
    public function testRemoveSkipsNullFileNameProperty()
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
    public function testResolvePath($protocol, $filesystemKey, $uploadDir, $expectedPath, $relative)
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

    public function pathProvider()
    {
        return array(
            //      protocol,   fs identifier,  upload dir, full path, relative
            array( 'gaufrette', 'filesystemKey', null,   'gaufrette://filesystemKey/file.txt', false ),
            array( 'data',      'filesystemKey', null,   'data://filesystemKey/file.txt', false ),
            array( 'gaufrette', 'filesystemKey', 'foo',  'gaufrette://filesystemKey/foo/file.txt', false ),
            array( 'gaufrette', 'filesystemKey', null,   'file.txt', true),
            array( 'gaufrette', 'filesystemKey', 'foo',  'foo/file.txt', true ),
        );
    }

    /**
     * Test the remove method does delete file from gaufrette filesystem
     */
    public function testThatRemoveMethodDoesDeleteFile()
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
     * Test that FileNotFound exception is catched
     */
    public function testRemoveNotFoundFile()
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

    public function testUploadSetsMetadataWhenUsingMetadataSupporterAdapter()
    {
        $filesystem = $this->getFilesystemMock();
        $file = $this->getUploadedFileMock();
        $adapter = $this->getMock('\Gaufrette\Adapter\MetadataSupporter');

        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('filename'));

        $file
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->getValidUploadDir() . DIRECTORY_SEPARATOR . 'test.txt'));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));

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

    public function testUploadDoesNotSetMetadataWhenUsingNonMetadataSupporterAdapter()
    {
        $adapter = $this->getMock('\Gaufrette\Adapter');
        $filesystem = $this->getFilesystemMock();
        $file = $this->getUploadedFileMock();

        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('filename'));

        $file
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->getValidUploadDir() . DIRECTORY_SEPARATOR . 'test.txt'));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));

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
            ->expects($this->never())
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

    /**
     * Creates a mock of gaufrette filesystem map.
     *
     * @return FilesystemMap The filesystem map.
     */
    protected function getFilesystemMapMock()
    {
        return $this
            ->getMockBuilder('Knp\Bundle\GaufretteBundle\FilesystemMap')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock of gaufrette filesystem.
     *
     * @return Filesystem The gaufrette filesystem object.
     */
    protected function getFilesystemMock()
    {
        return $this
            ->getMockBuilder('\Gaufrette\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
