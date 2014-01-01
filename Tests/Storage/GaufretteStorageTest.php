<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Gaufrette\Exception\FileNotFound;
use org\bovigo\vfs\vfsStream;

use Vich\UploaderBundle\Storage\GaufretteStorage;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * GaufretteStorageTest.
 *
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 */
class GaufretteStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory
     */
    protected $factory;

    /**
     * @var \Knp\Bundle\GaufretteBundle\FilesystemMap $factory
     */
    protected $filesystemMap;

    /**
     * @var vfsStreamDirectory
     */
    protected $root;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->factory = $this->getFactoryMock();
        $this->filesystemMap = $this->getFilesystemMapMock();
        $this->root = vfsStream::setup('vich_uploader_bundle', null, array(
            'uploads' => array(
                'file.txt'  => 'some content',
            ),
        ));
    }

    /**
     * Tests the upload method skips a mapping which has a null
     * uploadable property value.
     */
    public function testUploadSkipsMappingOnNullFile()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMappingMock();
        $mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue(null));

        $mapping
            ->expects($this->never())
            ->method('hasNamer');

        $mapping
            ->expects($this->never())
            ->method('getNamer');

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $storage->upload($obj, $mapping);
    }

    /**
     * Tests the upload method skips a mapping which has an uploadable
     * field property value that is not an instance of UploadedFile.
     */
    public function testUploadSkipsMappingOnNonUploadedFileInstance()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMappingMock();

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
            ->disableOriginalConstructor()
            ->getMock();

        $mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));

        $mapping
            ->expects($this->never())
            ->method('hasNamer');

        $mapping
            ->expects($this->never())
            ->method('getNamer');

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $storage->upload($obj, $mapping);
    }

    /**
     * Test the remove method skips trying to remove a file whose file name
     * property value returns null.
     */
    public function testRemoveSkipsNullFileNameProperty()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMappingMock();
        $mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue(null));

        $mapping
            ->expects($this->never())
            ->method('getUploadDestination');

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $storage->remove($obj, $mapping);
    }

    /**
     * Test the resolve path method.
     */
    public function testResolvePath()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMappingMock();

        $mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue('filesystemKey'));

        $mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($obj, 'file')
            ->will($this->returnValue($mapping));

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $path = $storage->resolvePath($obj, 'file');

        $this->assertEquals('gaufrette://filesystemKey/file.txt', $path);
    }

    /**
     * Test the resolve path method when the protocol parameter has been changed.
     */
    public function testResolvePathWithChangedProtocol()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMappingMock();

        $mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue('filesystemKey'));

        $mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($obj, 'file')
            ->will($this->returnValue($mapping));

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap, 'data');
        $path = $storage->resolvePath($obj, 'file');

        $this->assertEquals('data://filesystemKey/file.txt', $path);
    }

    /**
     * Test the remove method does delete file from gaufrette filesystem
     */
    public function testThatRemoveMethodDoesDeleteFile()
    {
        $mapping = $this->getMappingMock();
        $obj = new DummyEntity();

        $mapping
            ->expects($this->any())
            ->method('getUploadDestination')
            ->will($this->returnValue('filesystemKey'));
        $mapping
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

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $storage->remove($obj, $mapping);
    }

    /**
     * Test that FileNotFound exception is catched
     */
    public function testRemoveNotFoundFile()
    {
        $mapping = $this->getMappingMock();
        $obj = new DummyEntity();

        $mapping
            ->expects($this->any())
            ->method('getUploadDestination')
            ->will($this->returnValue('filesystemKey'));
        $mapping
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

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $storage->remove($obj, $mapping);
    }

    /**
     * Test the resolve path method throws exception
     * when an invaid field name is specified.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testResolvePathThrowsExceptionOnInvalidFieldName()
    {
        $obj = new DummyEntity();

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($obj, 'oops')
            ->will($this->returnValue(null));

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $storage->resolvePath($obj, 'oops');
    }

    public function testUploadSetsMetadataWhenUsingMetadataSupporterAdapter()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMappingMock();

        $adapter = $this->getMockBuilder('\Gaufrette\Adapter\MetadataSupporter')
            ->disableOriginalConstructor()
            ->setMethods(array('setMetadata', 'getMetadata'))
            ->getMock();

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();
        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('filename'));
        $file
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->getUploadDir() . DIRECTORY_SEPARATOR . 'file.txt'));

        $mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));

        $mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue('filesystemKey'));

        $filesystem = $this
            ->getMockBuilder('\Gaufrette\Filesystem')
            ->disableOriginalConstructor()
            ->setMethods(array('getAdapter', 'createStream', 'write'))
            ->getMock();

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

        $filesystemMap = $this
            ->getMockBuilder('Knp\Bundle\GaufretteBundle\FilesystemMap')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();

        $filesystemMap->expects($this->once())
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

        $storage = new GaufretteStorage($this->factory, $filesystemMap);
        $storage->upload($obj, $mapping);
    }

    public function testUploadDoesNotSetMetadataWhenUsingNonMetadataSupporterAdapter()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMappingMock();

        $adapter = $this->getMockBuilder('\Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->getMock();

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();
        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('filename'));
        $file
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->getUploadDir() . DIRECTORY_SEPARATOR . 'file.txt'));

        $mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));

        $mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue('filesystemKey'));

        $filesystem = $this
            ->getMockBuilder('\Gaufrette\Filesystem')
            ->disableOriginalConstructor()
            ->setMethods(array('getAdapter', 'createStream', 'write'))
            ->getMock();

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

        $filesystemMap = $this
            ->getMockBuilder('Knp\Bundle\GaufretteBundle\FilesystemMap')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();

        $filesystemMap->expects($this->once())
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

        $storage = new GaufretteStorage($this->factory, $filesystemMap);
        $storage->upload($obj, $mapping);
    }

    /**
     * Creates a mock factory.
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMappingFactory The factory.
     */
    protected function getFactoryMock()
    {
        return $this
            ->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMappingFactory')
            ->disableOriginalConstructor()
            ->getMock();
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

    protected function getMappingMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getUploadDir()
    {
        return $this->root->url() . DIRECTORY_SEPARATOR . 'uploads';
    }
}
