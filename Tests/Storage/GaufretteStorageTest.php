<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Vich\UploaderBundle\Storage\GaufretteStorage;
use Gaufrette\Exception\FileNotFound;
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
     * Sets up the test.
     */
    public function setUp()
    {
        $this->factory = $this->getFactoryMock();
        $this->filesystemMap = $this->getFilesystemMapMock();
    }

    /**
     * Tests the upload method skips a mapping which has a null
     * uploadable property value.
     */
    public function testUploadSkipsMappingOnNullFile()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $mapping
                ->expects($this->once())
                ->method('getPropertyValue')
                ->will($this->returnValue(null));

        $mapping
                ->expects($this->never())
                ->method('hasNamer');

        $mapping
                ->expects($this->never())
                ->method('getNamer');

        $mapping
                ->expects($this->never())
                ->method('getFileNameProperty');

        $this->factory
                ->expects($this->once())
                ->method('fromObject')
                ->with($obj)
                ->will($this->returnValue(array($mapping)));

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $storage->upload($obj);
    }

    /**
     * Tests the upload method skips a mapping which has an uploadable
     * field property value that is not an instance of UploadedFile.
     */
    public function testUploadSkipsMappingOnNonUploadedFileInstance()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
                ->disableOriginalConstructor()
                ->getMock();

        $mapping
                ->expects($this->once())
                ->method('getPropertyValue')
                ->will($this->returnValue($file));

        $mapping
                ->expects($this->never())
                ->method('hasNamer');

        $mapping
                ->expects($this->never())
                ->method('getNamer');

        $mapping
                ->expects($this->never())
                ->method('getFileNameProperty');

        $this->factory
                ->expects($this->once())
                ->method('fromObject')
                ->with($obj)
                ->will($this->returnValue(array($mapping)));

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $storage->upload($obj);
    }

    /**
     * Test the remove method does not remove a file that is configured
     * to not be deleted upon removal of the entity.
     */
    public function testRemoveSkipsConfiguredNotToDeleteOnRemove()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $mapping
                ->expects($this->once())
                ->method('getDeleteOnRemove')
                ->will($this->returnValue(false));

        $mapping
                ->expects($this->never())
                ->method('getFileNameProperty');

        $this->factory
                ->expects($this->once())
                ->method('fromObject')
                ->with($obj)
                ->will($this->returnValue(array($mapping)));

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $storage->remove($obj);
    }

    /**
     * Test the remove method skips trying to remove a file whose file name
     * property value returns null.
     */
    public function testRemoveSkipsNullFileNameProperty()
    {
        $obj = new DummyEntity();

        $prop = $this->getMockBuilder('\ReflectionProperty')
                ->disableOriginalConstructor()
                ->getMock();
        $prop
                ->expects($this->once())
                ->method('getValue')
                ->with($obj)
                ->will($this->returnValue(null));

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');
        $mapping
                ->expects($this->once())
                ->method('getDeleteOnRemove')
                ->will($this->returnValue(true));

        $mapping
                ->expects($this->once())
                ->method('getFileNameProperty')
                ->will($this->returnValue($prop));

        $mapping
                ->expects($this->never())
                ->method('getUploadDir');

        $this->factory
                ->expects($this->once())
                ->method('fromObject')
                ->with($obj)
                ->will($this->returnValue(array($mapping)));

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $storage->remove($obj);
    }

    /**
     * Test the resolve path method.
     */
    public function testResolvePath()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $prop = $this->getMockBuilder('\ReflectionProperty')
                ->disableOriginalConstructor()
                ->getMock();
        $prop
                ->expects($this->once())
                ->method('getValue')
                ->with($obj)
                ->will($this->returnValue('file.txt'));

        $mapping
                ->expects($this->once())
                ->method('getUploadDir')
                ->will($this->returnValue('filesystemKey'));

        $mapping
                ->expects($this->once())
                ->method('getFileNameProperty')
                ->will($this->returnValue($prop));

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
     * Test the remove method does delete file from gaufrette filesystem
     */
    public function testThatRemoveMethodDoesDeleteFile()
    {
        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');
        $obj = new DummyEntity();

        $prop = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();
        $prop
            ->expects($this->any())
            ->method('getValue')
            ->with($obj)
            ->will($this->returnValue('file.txt'));
        $prop
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('nameProperty'));

        $mapping
            ->expects($this->once())
            ->method('getDeleteOnRemove')
            ->will($this->returnValue(true));
        $mapping
            ->expects($this->any())
            ->method('getUploadDir')
            ->will($this->returnValue('filesystemKey'));
        $mapping
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue($prop));
        $mapping
            ->expects($this->once())
            ->method('getProperty')
            ->will($this->returnValue($prop));

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

        $this
            ->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($mapping)));

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $storage->remove($obj, 'file');
    }

    /**
     * Test that FileNotFound exception is catched
     */
    public function testRemoveNotFoundFile()
    {
        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');
        $obj = new DummyEntity();

        $prop = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();
        $prop
            ->expects($this->any())
            ->method('getValue')
            ->with($obj)
            ->will($this->returnValue('file.txt'));
        $prop
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('nameProperty'));

        $mapping
            ->expects($this->once())
            ->method('getDeleteOnRemove')
            ->will($this->returnValue(true));
        $mapping
            ->expects($this->any())
            ->method('getUploadDir')
            ->will($this->returnValue('filesystemKey'));
        $mapping
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue($prop));
        $mapping
            ->expects($this->once())
            ->method('getProperty')
            ->will($this->returnValue($prop));

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

        $this
            ->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($mapping)));

        $storage = new GaufretteStorage($this->factory, $this->filesystemMap);
        $storage->remove($obj, 'file');
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

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $adapter = $this->getMockBuilder('\Gaufrette\Adapter\MetadataSupporter')
            ->disableOriginalConstructor()
            ->setMethods(array('setMetadata', 'getMetadata'))
            ->getMock();

        $prop = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $prop
            ->expects($this->any())
            ->method('getValue')
            ->with($obj)
            ->will($this->returnValue('file.txt'));

        $prop
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('nameProperty'));

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
            ->will($this->returnValue(__DIR__ . '/../Fixtures/file.txt'));

        $mapping
            ->expects($this->once())
            ->method('getPropertyValue')
            ->will($this->returnValue($file));

        $mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue('filesystemKey'));

        $mapping
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue($prop));

        $mapping
            ->expects($this->once())
            ->method('getProperty')
            ->will($this->returnValue($prop));

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

        $this
            ->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($mapping)));

        $adapter
            ->expects($this->once())
            ->method('setMetadata')
            ->will($this->returnValue(null));

        $filesystem
            ->expects($this->any())
            ->method('getAdapter')
            ->will($this->returnValue($adapter));


        $storage = new GaufretteStorage($this->factory, $filesystemMap);
        $storage->upload($obj);
    }

    public function testUploadDoesNotSetMetadataWhenUsingNonMetadataSupporterAdapter()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $adapter = $this->getMockBuilder('\Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->getMock();

        $prop = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $prop
            ->expects($this->any())
            ->method('getValue')
            ->with($obj)
            ->will($this->returnValue('file.txt'));

        $prop
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('nameProperty'));

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
            ->will($this->returnValue(__DIR__ . '/../Fixtures/file.txt'));

        $mapping
            ->expects($this->once())
            ->method('getPropertyValue')
            ->will($this->returnValue($file));

        $mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue('filesystemKey'));

        $mapping
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue($prop));

        $mapping
            ->expects($this->once())
            ->method('getProperty')
            ->will($this->returnValue($prop));

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

        $this
            ->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($mapping)));

        $adapter
            ->expects($this->never())
            ->method('setMetadata')
            ->will($this->returnValue(null));

        $filesystem
            ->expects($this->any())
            ->method('getAdapter')
            ->will($this->returnValue($adapter));


        $storage = new GaufretteStorage($this->factory, $filesystemMap);
        $storage->upload($obj);
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
}
