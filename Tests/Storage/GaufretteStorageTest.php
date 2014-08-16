<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Gaufrette\Exception\FileNotFound;

use Vich\UploaderBundle\Storage\GaufretteStorage;

/**
 * GaufretteStorageTest.
 *
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 */
class GaufretteStorageTest extends StorageTestCase
{
    /**
     * @var \Knp\Bundle\GaufretteBundle\FilesystemMap $factory
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
    public function testResolvePath($protocol, $filesystemKey, $uploadDir, $expectedPath)
    {
        $this->mapping
            ->expects($this->once())
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
            ->method('fromName')
            ->with($this->object, 'file_mapping')
            ->will($this->returnValue($this->mapping));

        $this->storage = new GaufretteStorage($this->factory, $this->filesystemMap, $protocol);
        $path = $this->storage->resolvePath($this->object, 'file_mapping');

        $this->assertEquals($expectedPath, $path);
    }

    public function pathProvider()
    {
        return array(
            //      protocol,   fs identifier,  upload dir, full path
            array( 'gaufrette', 'filesystemKey', null,   'gaufrette://filesystemKey/file.txt' ),
            array( 'data',      'filesystemKey', null,   'data://filesystemKey/file.txt' ),
            array( 'gaufrette', 'filesystemKey', 'foo/', 'gaufrette://filesystemKey/foo/file.txt' ),
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
        $adapter = $this->getMockBuilder('\Gaufrette\Adapter\MetadataSupporter')
            ->disableOriginalConstructor()
            ->setMethods(array('setMetadata', 'getMetadata'))
            ->getMock();

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

        $imb = $this
            ->getMockBuilder('\Gaufrette\Stream\InMemoryBuffer')
            ->disableOriginalConstructor()
            ->setMethods(array('open', 'write', 'close'))
            ->getMock();

        $imb
            ->expects($this->once())
            ->method('open')
            ->will($this->returnValue(true));

        $imb
            ->expects($this->once())
            ->method('write')
            ->will($this->returnValue(true));

        $imb
            ->expects($this->once())
            ->method('close')
            ->will($this->returnValue(true));

        $filesystem
            ->expects($this->once())
            ->method('createStream')
            ->will($this->returnValue($imb));

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->will($this->returnValue($filesystem));

        $adapter
            ->expects($this->once())
            ->method('setMetadata')
            ->will($this->returnValue(null));

        $filesystem
            ->expects($this->any())
            ->method('getAdapter')
            ->will($this->returnValue($adapter));

        $this->storage->upload($this->object, $this->mapping);
    }

    public function testUploadDoesNotSetMetadataWhenUsingNonMetadataSupporterAdapter()
    {
        $adapter = $this->getMockBuilder('\Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->getMock();

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

        $filesystem = $this->getFilesystemMock();

        $imb = $this
            ->getMockBuilder('\Gaufrette\Stream\InMemoryBuffer')
            ->disableOriginalConstructor()
            ->setMethods(array('open', 'write', 'close'))
            ->getMock();

        $imb
            ->expects($this->once())
            ->method('open')
            ->will($this->returnValue(true));

        $imb
            ->expects($this->once())
            ->method('write')
            ->will($this->returnValue(true));

        $imb
            ->expects($this->once())
            ->method('close')
            ->will($this->returnValue(true));

        $filesystem
            ->expects($this->once())
            ->method('createStream')
            ->will($this->returnValue($imb));

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->will($this->returnValue($filesystem));

        $adapter
            ->expects($this->never())
            ->method('setMetadata')
            ->will($this->returnValue(null));

        $filesystem
            ->expects($this->any())
            ->method('getAdapter')
            ->will($this->returnValue($adapter));

        $this->storage->upload($this->object, $this->mapping);
    }

    /**
     * Creates a mock of gaufrette filesystem map.
     *
     * @return \Knp\Bundle\GaufretteBundle\FilesystemMap The filesystem map.
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
     * @return \Gaufrette\Filesystem The gaufrette filesystem object.
     */
    protected function getFilesystemMock()
    {
        return $this
            ->getMockBuilder('\Gaufrette\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
