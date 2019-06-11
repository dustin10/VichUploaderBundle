<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use Doctrine\Common\Persistence\Proxy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
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

    protected function setUp(): void
    {
        $this->container = $this->getContainerMock();
        $this->metadata = $this->getMetadataReaderMock();
    }

    /**
     * Tests that an exception is thrown if a non uploadable
     * object is passed in.
     */
    public function testFromObjectThrowsExceptionIfNotUploadable(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\NotUploadableException::class);

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->willReturn(false);

        $factory = new PropertyMappingFactory($this->container, $this->metadata, []);
        $factory->fromObject(new DummyEntity());
    }

    /**
     * Test the fromObject method with one uploadable
     * field.
     *
     * @dataProvider fromObjectProvider
     */
    public function testFromObjectOneField($object, $givenClassName, $expectedClassName): void
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
            ->willReturn(true);

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with($expectedClassName)
            ->willReturn([
                'file' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                ],
            ]);

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $mappings = $factory->fromObject($object, $givenClassName);

        $this->assertCount(1, $mappings);

        $mapping = \current($mappings);

        $this->assertEquals('dummy_file', $mapping->getMappingName());
        $this->assertEquals('images', $mapping->getUploadDestination());
        $this->assertNull($mapping->getNamer());
        $this->assertFalse($mapping->hasNamer());
    }

    public function fromObjectProvider(): array
    {
        $obj = new DummyEntity();
        $proxy = $this->createMock(Proxy::class);

        return [
            [$obj, null, DummyEntity::class],
            [$obj, DummyEntity::class, DummyEntity::class],
            [$proxy, DummyEntity::class, DummyEntity::class],
            [[], DummyEntity::class, DummyEntity::class],
        ];
    }

    public function testMappingCreationFailsIfTheClassNameCannotBeDetermined(): void
    {
        $this->expectException(\RuntimeException::class);

        $factory = new PropertyMappingFactory($this->container, $this->metadata, []);
        $factory->fromObject([]);
    }

    public function testFromObjectOneFieldWithNoExplicitFilenameProperty(): void
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
            ->with(DummyEntity::class)
            ->willReturn(true);

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with(DummyEntity::class)
            ->willReturn([
                'file' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'file',
                ],
            ]);

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $mappings = $factory->fromObject($obj);

        $this->assertCount(1, $mappings);

        $mapping = \current($mappings);

        $this->assertEquals('dummy_file', $mapping->getMappingName());
        $this->assertEquals('images', $mapping->getUploadDestination());
        $this->assertNull($mapping->getNamer());
        $this->assertFalse($mapping->hasNamer());
        $this->assertEquals('file_name', $mapping->getFileNamePropertyName());
    }

    public function testFromObjectWithExplicitMapping(): void
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
            ->with(DummyEntity::class)
            ->willReturn(true);

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with(DummyEntity::class)
            ->willReturn([
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
            ]);

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $mappings = $factory->fromObject(new DummyEntity(), null, 'other_mapping');

        $this->assertCount(1, $mappings);

        $mapping = \current($mappings);

        $this->assertEquals('other_mapping', $mapping->getMappingName());
    }

    /**
     * Test that an exception is thrown when an invalid mapping name
     * is specified.
     */
    public function testThrowsExceptionOnInvalidMappingName(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\MappingNotFoundException::class);

        $mappings = [
            'bad_name' => [],
        ];

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(true);

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with(DummyEntity::class)
            ->willReturn([
                'file' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                ],
            ]);

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $factory->fromObject(new DummyEntity());
    }

    /**
     * @dataProvider fromFieldProvider
     */
    public function testFromField($object, $className, $expectedClassName): void
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
            ->willReturn(true);

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableField')
            ->with($expectedClassName, 'file')
            ->willReturn([
                'mapping' => 'dummy_file',
                'propertyName' => 'file',
                'fileNameProperty' => 'fileName',
            ]
            );

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $mapping = $factory->fromField($object, 'file', $className);

        $this->assertEquals('dummy_file', $mapping->getMappingName());
    }

    public function fromFieldProvider(): array
    {
        $obj = new DummyEntity();
        $proxy = $this->createMock(Proxy::class);

        return [
            [$obj, null, DummyEntity::class],
            [$obj, DummyEntity::class, DummyEntity::class],
            [$proxy, DummyEntity::class, DummyEntity::class],
            [[], DummyEntity::class, DummyEntity::class],
        ];
    }

    /**
     * Test that the fromField method returns null when an invalid
     * field name is specified.
     */
    public function testFromFieldReturnsNullOnInvalidFieldName(): void
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(true);

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableField')
            ->with(DummyEntity::class)
            ->willReturn(null);

        $factory = new PropertyMappingFactory($this->container, $this->metadata, []);
        $mapping = $factory->fromField(new DummyEntity(), 'oops');

        $this->assertNull($mapping);
    }

    public function testCustomFileNameProperty(): void
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
            ->with(DummyEntity::class)
            ->willReturn(true);

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableField')
            ->with(DummyEntity::class)
            ->willReturn([
                'mapping' => 'dummy_file',
                'propertyName' => 'file',
            ]);

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings, '_suffix');
        $mapping = $factory->fromField(new DummyEntity(), 'file');

        $this->assertEquals('file_suffix', $mapping->getFileNamePropertyName());
    }

    public function testConfiguredNamersAreRetrievedFromContainer(): void
    {
        $mappings = [
            'dummy_file' => [
                'upload_destination' => 'images',
                'namer' => ['service' => 'my.custom.namer'],
                'directory_namer' => ['service' => 'my.custom.directory_namer'],
            ],
        ];

        $namer = $this->createMock(NamerInterface::class);
        $directoryNamer = $this->createMock(DirectoryNamerInterface::class);

        $this->container
            ->method('get')
            ->willReturnMap([
                ['my.custom.namer', /* invalid behavior */ 1, $namer],
                ['my.custom.directory_namer', /* invalid behavior */ 1, $directoryNamer],
            ]);

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(true);

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with(DummyEntity::class)
            ->willReturn([
                'file' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                ],
            ]);

        $factory = new PropertyMappingFactory($this->container, $this->metadata, $mappings);
        $mappings = $factory->fromObject(new DummyEntity());

        $this->assertCount(1, $mappings);

        $mapping = \current($mappings);

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
        return $this->createMock(ContainerInterface::class);
    }

    /**
     * Creates a mock metadata reader.
     *
     * @return MetadataReader The metadata reader
     */
    protected function getMetadataReaderMock()
    {
        return $this->getMockBuilder(MetadataReader::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
