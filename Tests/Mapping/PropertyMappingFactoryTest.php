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
     * @var \Vich\UploaderBundle\Metadata\MetadataReader $metadata
     */
    protected $metadata;

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
        $this->metadata = $this->getMetadataReaderMock();
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
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->will($this->returnValue(false));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $this->adapter, array());
        $factory->fromObject($obj);
    }

    /**
     * Test the fromObject method with one uploadable
     * field.
     */
    public function testFromObjectOneField()
    {
        $obj = new DummyEntity();

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

        $this->adapter
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(array(
                'file' => array(
                    'mapping'           => 'dummy_file',
                    'propertyName'      => 'file',
                    'fileNameProperty'  => 'fileName',
                )
            )));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $this->adapter, $mappings);
        $mappings = $factory->fromObject($obj);

        $this->assertEquals(1, count($mappings));

        $mapping = $mappings[0];

        $this->assertEquals('dummy_file', $mapping->getMappingName());
        $this->assertEquals('images', $mapping->getUploadDir());
        $this->assertNull($mapping->getNamer());
        $this->assertFalse($mapping->hasNamer());
        $this->assertTrue($mapping->getDeleteOnRemove());
        $this->assertTrue($mapping->getInjectOnLoad());
    }

    /**
     * Test the fromObject method with one uploadable
     * field, with the object classname given.
     */
    public function testFromObjectOneFieldWithClassName()
    {
        $obj = new DummyEntity();

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

        $this->adapter
            ->expects($this->never())
            ->method('getReflectionClass');

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(array(
                'file' => array(
                    'mapping' => 'dummy_file',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                )
            )));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $this->adapter, $mappings);
        $mappings = $factory->fromObject($obj, 'Vich\UploaderBundle\Tests\DummyEntity');

        $this->assertEquals(1, count($mappings));

        $mapping = $mappings[0];

        $this->assertEquals('dummy_file', $mapping->getMappingName());
        $this->assertEquals('images', $mapping->getUploadDir());
        $this->assertNull($mapping->getNamer());
        $this->assertFalse($mapping->hasNamer());
        $this->assertTrue($mapping->getDeleteOnRemove());
        $this->assertTrue($mapping->getInjectOnLoad());
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

        $mappings = array(
            'bad_name' => array()
        );

        $this->adapter
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(array(
                'file' => array(
                    'mapping'           => 'dummy_file',
                    'propertyName'      => 'file',
                    'fileNameProperty'  => 'fileName',
                )
            )));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $this->adapter, $mappings);
        $mappings = $factory->fromObject($obj);
    }

    /**
     * Test that the fromField method returns null when an invalid
     * field name is specified.
     */
    public function testFromFieldReturnsNullOnInvalidFieldName()
    {
        $obj = new DummyEntity();

        $this->adapter
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableField')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(null));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $this->adapter, array());
        $mapping = $factory->fromField($obj, 'oops');

        $this->assertNull($mapping);
    }

    public function testConfiguredNamerRetrievedFromContainer()
    {
        $obj = new DummyEntity();

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

        $namer = $this->getMock('Vich\UploaderBundle\Naming\NamerInterface');

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('my.custom.namer')
            ->will($this->returnValue($namer));

        $this->adapter
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(array(
                'file' => array(
                    'mapping'           => 'dummy_file',
                    'propertyName'      => 'file',
                    'fileNameProperty'  => 'fileName',
                )
            )));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $this->adapter, $mappings);
        $mappings = $factory->fromObject($obj);

        $this->assertEquals(1, count($mappings));

        $mapping = $mappings[0];

        $this->assertEquals($namer, $mapping->getNamer());
        $this->assertTrue($mapping->hasNamer());
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
     * Creates a mock metadata reader.
     *
     * @return \Vich\UploaderBundle\Metadata\MetadataReader The metadata reader.
     */
    protected function getMetadataReaderMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Metadata\MetadataReader')
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
