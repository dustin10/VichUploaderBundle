<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * PropertyMappingFactoryTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class PropertyMappingFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var MetadataReader $metadata
     */
    protected $metadata;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->container = $this->getContainerMock();
        $this->metadata = $this->getMetadataReaderMock();
    }

    /**
     * Tests that an exception is thrown if a non uploadable
     * object is passed in.
     *
     * @expectedException \Vich\UploaderBundle\Exception\NotUploadableException
     */
    public function testFromObjectThrowsExceptionIfNotUploadable()
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->will($this->returnValue(false));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, array());
        $factory->fromObject(new DummyEntity());
    }

    /**
     * Test the fromObject method with one uploadable
     * field.
     *
     * @dataProvider fromObjectProvider
     */
    public function testFromObjectOneField($object, $givenClassName, $expectedClassName)
    {
        $mappings = array(
            'dummy_file' => array(
                'upload_destination' => 'images',
                'namer'              => null,
                'directory_namer'    => null
            )
        );

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($expectedClassName)
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with($expectedClassName)
            ->will($this->returnValue(array(
                'file' => array(
                    'mapping'           => 'dummy_file',
                    'propertyName'      => 'file',
                    'fileNameProperty'  => 'fileName',
                )
            )));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $mappings = $factory->fromObject($object, $givenClassName);

        $this->assertEquals(1, count($mappings));

        $mapping = current($mappings);

        $this->assertEquals('dummy_file', $mapping->getMappingName());
        $this->assertEquals('images', $mapping->getUploadDestination());
        $this->assertNull($mapping->getNamer());
        $this->assertFalse($mapping->hasNamer());
    }

    public function fromObjectProvider()
    {
        $obj = new DummyEntity();
        $proxy = $this->getMock('Doctrine\Common\Persistence\Proxy');

        return array(
            array( $obj, null, 'Vich\UploaderBundle\Tests\DummyEntity' ),
            array( $obj, 'Vich\UploaderBundle\Tests\DummyEntity', 'Vich\UploaderBundle\Tests\DummyEntity' ),
            array( $proxy, 'Vich\UploaderBundle\Tests\DummyEntity', 'Vich\UploaderBundle\Tests\DummyEntity' ),
            array( array(), 'Vich\UploaderBundle\Tests\DummyEntity', 'Vich\UploaderBundle\Tests\DummyEntity' ),
        );
    }

    /**
     * @expectedException RuntimeException
     */
    public function testMappingCreationFailsIfTheClassNameCannotBeDetermined()
    {
        $factory = new PropertyMappingFactory($this->container, $this->metadata, array());
        $factory->fromObject(array());
    }

    public function testFromObjectOneFieldWithNoExplicitFilenameProperty()
    {
        $obj = new DummyEntity();

        $mappings = array(
            'dummy_file' => array(
                'upload_destination' => 'images',
                'namer'              => null,
                'directory_namer'    => null
            )
        );

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
                    'mapping'       => 'dummy_file',
                    'propertyName'  => 'file',
                )
            )));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $mappings = $factory->fromObject($obj);

        $this->assertEquals(1, count($mappings));

        $mapping = current($mappings);

        $this->assertEquals('dummy_file', $mapping->getMappingName());
        $this->assertEquals('images', $mapping->getUploadDestination());
        $this->assertNull($mapping->getNamer());
        $this->assertFalse($mapping->hasNamer());
        $this->assertEquals('file_name', $mapping->getFileNamePropertyName());
    }

    public function testFromObjectWithExplicitMapping()
    {
        $mappings = array(
            'dummy_mapping' => array(
                'upload_destination' => 'images',
                'namer'              => null,
                'directory_namer'    => null
            ),
            'other_mapping' => array(
                'upload_destination' => 'documents',
                'namer'              => null,
                'directory_namer'    => null
            )
        );

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
                    'mapping'       => 'dummy_file',
                    'propertyName'  => 'file',
                ),
                'other_file' => array(
                    'mapping'       => 'dummy_file',
                    'propertyName'  => 'other_file',
                ),
                'document' => array(
                    'mapping'       => 'other_mapping',
                    'propertyName'  => 'document',
                ),
            )));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $mappings = $factory->fromObject(new DummyEntity(), null, 'other_mapping');

        $this->assertEquals(1, count($mappings));

        $mapping = current($mappings);

        $this->assertEquals('other_mapping', $mapping->getMappingName());
    }

    /**
     * Test that an exception is thrown when an invalid mapping name
     * is specified.
     *
     * @expectedException \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function testThrowsExceptionOnInvalidMappingName()
    {
        $mappings = array(
            'bad_name' => array()
        );

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

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $factory->fromObject(new DummyEntity());
    }

    /**
     * @dataProvider fromFieldProvider
     */
    public function testFromField($object, $className, $expectedClassName)
    {
        $mappings = array(
            'dummy_file' => array(
                'upload_destination' => 'images',
                'namer'              => null,
                'directory_namer'    => null
            )
        );

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($expectedClassName)
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableField')
            ->with($expectedClassName, 'file')
            ->will($this->returnValue(array(
                'mapping'           => 'dummy_file',
                'propertyName'      => 'file',
                'fileNameProperty'  => 'fileName',
            )
            ));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $mapping = $factory->fromField($object, 'file', $className);

        $this->assertEquals('dummy_file', $mapping->getMappingName());
    }

    public function fromFieldProvider()
    {
        $obj = new DummyEntity();
        $proxy = $this->getMock('Doctrine\Common\Persistence\Proxy');

        return array(
            array( $obj, null, 'Vich\UploaderBundle\Tests\DummyEntity' ),
            array( $obj, 'Vich\UploaderBundle\Tests\DummyEntity', 'Vich\UploaderBundle\Tests\DummyEntity' ),
            array( $proxy, 'Vich\UploaderBundle\Tests\DummyEntity', 'Vich\UploaderBundle\Tests\DummyEntity' ),
            array( array(), 'Vich\UploaderBundle\Tests\DummyEntity', 'Vich\UploaderBundle\Tests\DummyEntity' ),
        );
    }

    /**
     * Test that the fromField method returns null when an invalid
     * field name is specified.
     */
    public function testFromFieldReturnsNullOnInvalidFieldName()
    {
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

        $factory = new PropertyMappingFactory($this->container, $this->metadata, array());
        $mapping = $factory->fromField(new DummyEntity(), 'oops');

        $this->assertNull($mapping);
    }

    public function testCustomFileNameProperty()
    {
        $mappings = array(
            'dummy_file' => array(
                'namer' => false,
                'directory_namer' => false
            )
        );

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableField')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(array(
                'mapping'           => 'dummy_file',
                'propertyName'      => 'file'
            )));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings, '_suffix');
        $mapping = $factory->fromField(new DummyEntity(), 'file');

        $this->assertEquals('file_suffix', $mapping->getFileNamePropertyName());
    }

    public function testConfiguredNamersAreRetrievedFromContainer()
    {
        $mappings = array(
            'dummy_file' => array(
                'upload_destination' => 'images',
                'namer'              => array('service' => 'my.custom.namer'),
                'directory_namer'    => array('service' => 'my.custom.directory_namer'),
            )
        );

        $namer = $this->getMock('Vich\UploaderBundle\Naming\NamerInterface');
        $directoryNamer = $this->getMock('Vich\UploaderBundle\Naming\DirectoryNamerInterface');

        $this->container
            ->method('get')
            ->will($this->returnValueMap(array(
                array('my.custom.namer', /* invalid behavior */ 1, $namer),
                array('my.custom.directory_namer', /* invalid behavior */ 1, $directoryNamer),
            )));

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

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $mappings = $factory->fromObject(new DummyEntity());

        $this->assertEquals(1, count($mappings));

        $mapping = current($mappings);

        $this->assertEquals($namer, $mapping->getNamer());
        $this->assertTrue($mapping->hasNamer());
        $this->assertEquals($directoryNamer, $mapping->getDirectoryNamer());
        $this->assertTrue($mapping->hasDirectoryNamer());
    }

    /**
     * Creates a mock container.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface The container.
     */
    protected function getContainerMock()
    {
        return $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
    }

    /**
     * Creates a mock metadata reader.
     *
     * @return MetadataReader The metadata reader.
     */
    protected function getMetadataReaderMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Metadata\MetadataReader')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
