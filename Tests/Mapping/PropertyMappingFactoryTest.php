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
     * @var DummyEntity
     */
    protected $object;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->container = $this->getContainerMock();
        $this->metadata = $this->getMetadataReaderMock();
        $this->adapter = $this->getAdapterMock();

        $this->object = new DummyEntity();
    }

    /**
     * Tests that an exception is thrown if a non uploadable object is passed.
     *
     * @expectedException           InvalidArgumentException
     * @expectedExceptionMessage    The object is not uploadable.
     */
    public function testFromObjectThrowsExceptionIfNotUploadable()
    {
        $this->adapter
            ->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('stdClass'));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->will($this->returnValue(false));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $this->adapter, array());
        $factory->fromObject(new \stdClass());
    }

    /**
     * @dataProvider uploadableClassNameProvider
     */
    public function testFromObjectWithValidMapping($givenClassName, $inferredClassName)
    {
        $mappings = array(
            'dummy_file' => array(
                'upload_destination'    => 'images',
                'delete_on_remove'      => true,
                'delete_on_update'      => true,
                'namer'                 => null,
                'inject_on_load'        => true,
                'directory_namer'       => null
            )
        );

        if ($givenClassName === null) {
            $this->adapter
                ->expects($this->once())
                ->method('getClassName')
                ->will($this->returnValue($inferredClassName));
        } else {
            $this->adapter
                ->expects($this->never())
                ->method('getClassName');
        }

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($inferredClassName)
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with($inferredClassName)
            ->will($this->returnValue(array(
                'file' => array(
                    'mapping'           => 'dummy_file',
                    'propertyName'      => 'file',
                    'fileNameProperty'  => 'fileName',
                )
            )));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $this->adapter, $mappings);
        $mappings = $factory->fromObject($this->object, $givenClassName);

        $this->assertEquals(1, count($mappings));

        $mapping = $mappings[0];

        $this->assertEquals('dummy_file', $mapping->getMappingName());
        $this->assertEquals('images', $mapping->getUploadDestination());
        $this->assertNull($mapping->getNamer());
        $this->assertFalse($mapping->hasNamer());
        $this->assertTrue($mapping->getDeleteOnRemove());
        $this->assertTrue($mapping->getInjectOnLoad());
    }

    public function uploadableClassNameProvider()
    {
        $uploadableClassName = 'Vich\UploaderBundle\Tests\DummyEntity';

        return array(
            //      given className,        inferred className
            array(  null,                   $uploadableClassName),
            array(  $uploadableClassName,   $uploadableClassName),
        );
    }

    /**
     * Test that an exception is thrown when an invalid mapping name
     * is specified.
     *
     * @expectedException           InvalidArgumentException
     * @expectedExceptionMessage    No mapping named "dummy_file" configured.
     */
    public function testThrowsExceptionOnInvalidMappingName()
    {
        $mappings = array(
            'bad_name' => array()
        );

        $this->adapter
            ->expects($this->once())
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
        $factory->fromObject($this->object);
    }

    /**
     * Test that the fromField method returns null when an invalid field name
     * is specified.
     */
    public function testFromFieldReturnsNullOnInvalidFieldName()
    {
        $this->adapter
            ->expects($this->once())
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
        $mapping = $factory->fromField($this->object, 'oops');

        $this->assertNull($mapping);
    }

    public function testConfiguredNamerRetrievedFromContainer()
    {
        $mappings = array(
            'dummy_file' => array(
                'upload_destination'    => 'images',
                'delete_on_remove'      => true,
                'delete_on_update'      => true,
                'namer'                 => 'my.custom.namer',
                'inject_on_load'        => true,
                'directory_namer'       => null
            )
        );

        $namer = $this->getMock('Vich\UploaderBundle\Naming\NamerInterface');

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('my.custom.namer')
            ->will($this->returnValue($namer));

        $this->adapter
            ->expects($this->once())
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
        $mappings = $factory->fromObject($this->object);

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
