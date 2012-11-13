<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * PropertyMappingFactoryTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class PropertyMappingFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    protected $container;

    /**
     * @var \Vich\UploaderBundle\Driver\AnnotationDriver $driver
     */
    protected $driver;

    /**
     * @var \Vich\UploaderBundle\Adapter\AdapterInterface $adapter
     */
    protected $adapter;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->container = $this->getContainerMock();
        $this->driver = $this->getDriverMock();
        $this->adapter = $this->getAdapterMock();
    }

    /**
     * Tests that an exception is thrown if a non uploadable
     * object is passed in.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testFromObjectThrowsExceptionIfNotUploadable()
    {
        $obj = new \StdClass();

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue(new \ReflectionClass($obj)));

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->will($this->returnValue(null));

        $factory = new PropertyMappingFactory($this->container, $this->driver, $this->adapter, array());
        $factory->fromObject($obj);
    }

    /**
     * Test the fromObject method with one uploadable
     * field.
     */
    public function testFromObjectOneField()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $mappings = array(
            'dummy_file' => array(
                'upload_destination' => 'images',
                'delete_on_remove' => true,
                'delete_on_update' => true,
                'namer' => null,
                'inject_on_load' => true,
                'directory_namer' => null
            )
        );

        $uploadable = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\Uploadable')
                      ->disableOriginalConstructor()
                      ->getMock();

        $fileField = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\UploadableField')
                     ->disableOriginalConstructor()
                     ->getMock();

        $fileField
            ->expects($this->any())
            ->method('getMapping')
            ->will($this->returnValue('dummy_file'));

        $fileField
            ->expects($this->once())
            ->method('getPropertyName')
            ->will($this->returnValue('file'));

        $fileField
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue('fileName'));

        $this->adapter
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->driver
            ->expects($this->once())
            ->method('readUploadableFields')
            ->with($class)
            ->will($this->returnValue(array($fileField)));

        $factory = new PropertyMappingFactory($this->container, $this->driver, $this->adapter, $mappings);
        $mappings = $factory->fromObject($obj);

        $this->assertEquals(1, count($mappings));

        if (count($mappings) > 0) {
            $mapping = $mappings[0];

            $this->assertEquals('dummy_file', $mapping->getMappingName());
            $this->assertEquals('images', $mapping->getUploadDir());
            $this->assertNull($mapping->getNamer());
            $this->assertFalse($mapping->hasNamer());
            $this->assertTrue($mapping->getDeleteOnRemove());
            $this->assertTrue($mapping->getInjectOnLoad());
        }
    }

    /**
     * Test that an exception is thrown when an invalid mapping name
     * is specified.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionOnInvalidMappingName()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $mappings = array(
            'bad_name' => array()
        );

        $uploadable = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\Uploadable')
                      ->disableOriginalConstructor()
                      ->getMock();

        $fileField = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\UploadableField')
                     ->disableOriginalConstructor()
                     ->getMock();

        $fileField
            ->expects($this->any())
            ->method('getMapping')
            ->will($this->returnValue('dummy_file'));

        $this->adapter
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->driver
            ->expects($this->once())
            ->method('readUploadableFields')
            ->with($class)
            ->will($this->returnValue(array($fileField)));

        $factory = new PropertyMappingFactory($this->container, $this->driver, $this->adapter, $mappings);
        $mappings = $factory->fromObject($obj);
    }

    /**
     * Test that the fromField method returns null when an invalid
     * field name is specified.
     */
    public function testFromFieldReturnsNullOnInvalidFieldName()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $uploadable = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\Uploadable')
                      ->disableOriginalConstructor()
                      ->getMock();

        $this->adapter
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->driver
            ->expects($this->once())
            ->method('readUploadableField')
            ->with($class)
            ->will($this->returnValue(null));

        $factory = new PropertyMappingFactory($this->container, $this->driver, $this->adapter, array());
        $mapping = $factory->fromField($obj, 'oops');

        $this->assertNull($mapping);
    }

    public function testConfiguredNamerRetrievedFromContainer()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $mappings = array(
            'dummy_file' => array(
                'upload_destination' => 'images',
                'delete_on_remove' => true,
                'delete_on_update' => true,
                'namer' => 'my.custom.namer',
                'inject_on_load' => true,
                'directory_namer' => null
            )
        );

        $uploadable = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\Uploadable')
                      ->disableOriginalConstructor()
                      ->getMock();

        $fileField = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\UploadableField')
                     ->disableOriginalConstructor()
                     ->getMock();

        $fileField
            ->expects($this->any())
            ->method('getMapping')
            ->will($this->returnValue('dummy_file'));

        $fileField
            ->expects($this->once())
            ->method('getPropertyName')
            ->will($this->returnValue('file'));

        $fileField
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue('fileName'));

        $namer = $this->getMock('Vich\UploaderBundle\Naming\NamerInterface');

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('my.custom.namer')
            ->will($this->returnValue($namer));

        $this->adapter
            ->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->driver
            ->expects($this->once())
            ->method('readUploadableFields')
            ->with($class)
            ->will($this->returnValue(array($fileField)));

        $factory = new PropertyMappingFactory($this->container, $this->driver, $this->adapter, $mappings);
        $mappings = $factory->fromObject($obj);

        $this->assertEquals(1, count($mappings));

        if (count($mappings) > 0) {
            $mapping = $mappings[0];

            $this->assertEquals($namer, $mapping->getNamer());
            $this->assertTrue($mapping->hasNamer());
        }
    }

    /**
     * Creates a mock container.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface The container.
     */
    protected function getContainerMock()
    {
        return $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
               ->disableOriginalConstructor()
               ->getMock();
    }

    /**
     * Creates a mock annotation driver.
     *
     * @return \Vich\UploaderBundle\Driver\AnnotationDriver The driver.
     */
    protected function getDriverMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Driver\AnnotationDriver')
               ->disableOriginalConstructor()
               ->getMock();
    }

    /**
     * Creates a mock adapter.
     *
     * @return \Vich\UploaderBundle\Adapter\AdapterInterface The adapter.
     */
    protected function getAdapterMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Adapter\AdapterInterface')
               ->disableOriginalConstructor()
               ->getMock();
    }
}
