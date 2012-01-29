<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Vich\UploaderBundle\Storage\FileSystemStorage;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * FileSystemStorageTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class FileSystemStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory
     */
    protected $factory;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->factory = $this->getFactoryMock();
    }

    /**
     * Tests the upload method.
     */
    public function testUpload()
    {

    }

    /**
     * Test the remove method.
     */
    public function testRemove()
    {

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
            ->will($this->returnValue('/tmp'));

        $mapping
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue($prop));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($obj, 'file')
            ->will($this->returnValue($mapping));

        $storage = new FileSystemStorage($this->factory);
        $path = $storage->resolvePath($obj, 'file');

        $this->assertEquals('/tmp/file.txt', $path);
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

        $storage = new FileSystemStorage($this->factory);
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
}
