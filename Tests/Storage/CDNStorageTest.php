<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Vich\UploaderBundle\Storage\CDNStorage;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Storage\Adapter\CDNAdapterInterface;

/**
 * FileSystemStorageTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class CDNStorageTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory
     */
    protected $factory;

    /**
     *
     * @var \VichUploaderBundle\Storage\Adapter\CDNAdapterInterface 
     */
    protected $cdnAdapter;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->factory = $this->getFactoryMock();
        $this->cdnAdapter = $this->getAdapterMock();
    }

    /**
     * Tests the interaction with CDNAdapterInterface in real upload 
     */
    public function testUploadToCdn()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
                ->disableOriginalConstructor()
                ->getMock();
        $file->expects($this->once())
                ->method('getClientOriginalName')
                ->will($this->returnValue('fooOriginalName'));
        $file->expects($this->once())
                ->method('getPathName')
                ->will($this->returnValue('uploadedTmpFilePath'));

        $prop = $this->getMockBuilder('\ReflectionProperty')
                ->disableOriginalConstructor()
                ->getMock();
        $prop
                ->expects($this->once())
                ->method('setValue')
                ->with($obj, 'fooOriginalName');
        $mapping
                ->expects($this->once())
                ->method('getPropertyValue')
                ->will($this->returnValue($file));

        $mapping
                ->expects($this->once())
                ->method('hasNamer')
                ->will($this->returnValue(false));

        $mapping
                ->expects($this->never())
                ->method('getNamer');

        $mapping
                ->expects($this->once())
                ->method('getFileNameProperty')
                ->will($this->returnValue($prop));

        $this->factory
                ->expects($this->once())
                ->method('fromObject')
                ->with($obj)
                ->will($this->returnValue(array($mapping)));

        $this->cdnAdapter
                ->expects($this->once())
                ->method('put');

        $storage = new CDNStorage($this->factory, $this->cdnAdapter);
        $storage->upload($obj);
    }
    
    public function testRemove()
    {
        
        $obj = new DummyEntity();

        $prop = $this->getMockBuilder('\ReflectionProperty')
                ->disableOriginalConstructor()
                ->getMock();
        
        $prop
                ->expects($this->once())
                ->method('getValue')
                ->with($obj)
                ->will($this->returnValue('fooOriginalName'));
        
        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $mapping
                ->expects($this->once())
                ->method('getDeleteOnRemove')
                ->will($this->returnValue(true));

        $mapping
                ->expects($this->once())
                ->method('getFileNameProperty')
                ->will($this->returnValue($prop));

        $this->factory
                ->expects($this->once())
                ->method('fromObject')
                ->with($obj)
                ->will($this->returnValue(array($mapping)));
        
        $storage = new CDNStorage($this->factory, $this->cdnAdapter);
        $storage->remove($obj);
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

        $storage = new CDNStorage($this->factory, $this->cdnAdapter);
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

        $storage = new CDNStorage($this->factory, $this->cdnAdapter);
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

        $storage = new CDNStorage($this->factory, $this->cdnAdapter);
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

        $storage = new CDNStorage($this->factory, $this->cdnAdapter);
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
                ->expects($this->never())
                ->method('getUploadDir');

        $mapping
                ->expects($this->once())
                ->method('getFileNameProperty')
                ->will($this->returnValue($prop));

        $this->factory
                ->expects($this->once())
                ->method('fromField')
                ->with($obj, 'file')
                ->will($this->returnValue($mapping));

        $this->cdnAdapter
                ->expects($this->once())
                ->method('getAbsoluteUri')
                ->with('file.txt')
                ->will($this->returnValue('http://testcdn.com/file.txt'));
        
        $storage = new CDNStorage($this->factory, $this->cdnAdapter);
        $path = $storage->resolvePath($obj, 'file');

        $this->assertEquals('http://testcdn.com/file.txt', $path);
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

        $storage = new CDNStorage($this->factory, $this->cdnAdapter);
        $storage->resolvePath($obj, 'oops');
    }

    /**
     * Creates a mock factory.
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMappingFactory The factory.
     */
    protected function getFactoryMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMappingFactory')
                        ->disableOriginalConstructor()
                        ->getMock();
    }

    /**
     *
     * @return \VichUploaderBundle\Storage\Adapter\CDNAdapterInterface
     */
    protected function getAdapterMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Storage\Adapter\CDNAdapterInterface')
                        ->getMock();
    }

}