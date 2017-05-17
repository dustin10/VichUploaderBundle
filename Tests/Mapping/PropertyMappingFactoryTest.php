<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * PropertyMappingFactoryTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class PropertyMappingFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var MetadataReader
     */
    protected $metadata;

    /**
     * Sets up the test.
     */
    protected function setUp()
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

        $factory = new PropertyMappingFactory($this->container, $this->metadata, []);
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
        $mappings = [
            'dummy_file' => [
                'upload_destination' => 'images',
                'namer' => null,
                'directory_namer' => null,
            ],
        ];

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($expectedClassName)
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with($expectedClassName)
            ->will($this->returnValue([
                'file' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                ],
            ]));

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
        $proxy = $this->createMock('Doctrine\Common\Persistence\Proxy');

        return [
            [$obj, null, 'Vich\UploaderBundle\Tests\DummyEntity'],
            [$obj, 'Vich\UploaderBundle\Tests\DummyEntity', 'Vich\UploaderBundle\Tests\DummyEntity'],
            [$proxy, 'Vich\UploaderBundle\Tests\DummyEntity', 'Vich\UploaderBundle\Tests\DummyEntity'],
            [[], 'Vich\UploaderBundle\Tests\DummyEntity', 'Vich\UploaderBundle\Tests\DummyEntity'],
        ];
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testMappingCreationFailsIfTheClassNameCannotBeDetermined()
    {
        $factory = new PropertyMappingFactory($this->container, $this->metadata, []);
        $factory->fromObject([]);
    }

    public function testFromObjectOneFieldWithNoExplicitFilenameProperty()
    {
        $obj = new DummyEntity();

        $mappings = [
            'dummy_file' => [
                'upload_destination' => 'images',
                'namer' => null,
                'directory_namer' => null,
            ],
        ];

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue([
                'file' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'file',
                ],
            ]));

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
        $mappings = [
            'dummy_mapping' => [
                'upload_destination' => 'images',
                'namer' => null,
                'directory_namer' => null,
            ],
            'other_mapping' => [
                'upload_destination' => 'documents',
                'namer' => null,
                'directory_namer' => null,
            ],
        ];

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue([
                'file' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'file',
                ],
                'other_file' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'other_file',
                ],
                'document' => [
                    'mapping' => 'other_mapping',
                    'propertyName' => 'document',
                ],
            ]));

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
        $mappings = [
            'bad_name' => [],
        ];

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue([
                'file' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                ],
            ]));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $factory->fromObject(new DummyEntity());
    }

    /**
     * @dataProvider fromFieldProvider
     */
    public function testFromField($object, $className, $expectedClassName)
    {
        $mappings = [
            'dummy_file' => [
                'upload_destination' => 'images',
                'namer' => null,
                'directory_namer' => null,
            ],
        ];

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($expectedClassName)
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableField')
            ->with($expectedClassName, 'file')
            ->will($this->returnValue([
                'mapping' => 'dummy_file',
                'propertyName' => 'file',
                'fileNameProperty' => 'fileName',
            ]
            ));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $mapping = $factory->fromField($object, 'file', $className);

        $this->assertEquals('dummy_file', $mapping->getMappingName());
    }

    public function fromFieldProvider()
    {
        $obj = new DummyEntity();
        $proxy = $this->createMock('Doctrine\Common\Persistence\Proxy');

        return [
            [$obj, null, 'Vich\UploaderBundle\Tests\DummyEntity'],
            [$obj, 'Vich\UploaderBundle\Tests\DummyEntity', 'Vich\UploaderBundle\Tests\DummyEntity'],
            [$proxy, 'Vich\UploaderBundle\Tests\DummyEntity', 'Vich\UploaderBundle\Tests\DummyEntity'],
            [[], 'Vich\UploaderBundle\Tests\DummyEntity', 'Vich\UploaderBundle\Tests\DummyEntity'],
        ];
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

        $factory = new PropertyMappingFactory($this->container, $this->metadata, []);
        $mapping = $factory->fromField(new DummyEntity(), 'oops');

        $this->assertNull($mapping);
    }

    public function testCustomFileNameProperty()
    {
        $mappings = [
            'dummy_file' => [
                'namer' => false,
                'directory_namer' => false,
            ],
        ];

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableField')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue([
                'mapping' => 'dummy_file',
                'propertyName' => 'file',
            ]));

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings, '_suffix');
        $mapping = $factory->fromField(new DummyEntity(), 'file');

        $this->assertEquals('file_suffix', $mapping->getFileNamePropertyName());
    }

    public function testConfiguredNamersAreRetrievedFromContainer()
    {
        $mappings = [
            'dummy_file' => [
                'upload_destination' => 'images',
                'namer' => ['service' => 'my.custom.namer'],
                'directory_namer' => ['service' => 'my.custom.directory_namer'],
            ],
        ];

        $namer = $this->createMock('Vich\UploaderBundle\Naming\NamerInterface');
        $directoryNamer = $this->createMock('Vich\UploaderBundle\Naming\DirectoryNamerInterface');

        $this->container
            ->method('get')
            ->will($this->returnValueMap([
                ['my.custom.namer', /* invalid behavior */ 1, $namer],
                ['my.custom.directory_namer', /* invalid behavior */ 1, $directoryNamer],
            ]));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue([
                'file' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                ],
            ]));

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
     * @return \Symfony\Component\DependencyInjection\ContainerInterface The container
     */
    protected function getContainerMock()
    {
        return $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
    }

    /**
     * Creates a mock metadata reader.
     *
     * @return MetadataReader The metadata reader
     */
    protected function getMetadataReaderMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Metadata\MetadataReader')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
