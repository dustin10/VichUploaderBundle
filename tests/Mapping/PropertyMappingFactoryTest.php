<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use Doctrine\Persistence\Proxy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vich\UploaderBundle\Exception\NotUploadableException;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Mapping\PropertyMappingResolver;
use Vich\UploaderBundle\Metadata\MetadataReaderInterface;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class PropertyMappingFactoryTest extends TestCase
{
    protected ContainerInterface|MockObject $container;

    protected MockObject|MetadataReaderInterface $metadata;

    protected function setUp(): void
    {
        $this->metadata = $this->getMetadataReaderMock();
    }

    /**
     * Tests that an exception is thrown if a non uploadable
     * object is passed in.
     */
    public function testFromObjectThrowsExceptionIfNotUploadable(): void
    {
        $this->expectException(NotUploadableException::class);

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->willReturn(false);

        $resolver = new PropertyMappingResolver([], [], []);
        $factory = new PropertyMappingFactory($this->metadata, $resolver);
        $factory->fromObject(new DummyEntity());
    }

    /**
     * Test the fromObject method with one uploadable field.
     */
    #[DataProvider('fromObjectProvider')]
    public function testFromObjectOneField(object|array $object, ?string $givenClassName, string $expectedClassName): void
    {
        $mappings = [
            'dummy_file' => [
                'upload_destination' => 'images',
                'namer' => null,
                'directory_namer' => null,
            ],
        ];

        $expectedFields = [
            'file' => [
                'mapping' => 'dummy_file',
                'propertyName' => 'file',
                'fileNameProperty' => 'fileName',
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
            ->willReturn($expectedFields);

        $resolver = new PropertyMappingResolver([], [], $mappings);
        $factory = new PropertyMappingFactory($this->metadata, $resolver);
        $mappings = $factory->fromObject($object, $givenClassName);

        self::assertCount(1, $mappings);

        $mapping = \current($mappings);
        $mapping->setNamer($this->createMock(NamerInterface::class));

        self::assertEquals('dummy_file', $mapping->getMappingName());
        self::assertEquals('images', $mapping->getUploadDestination());
    }

    public static function fromObjectProvider(): array
    {
        $obj = new DummyEntity();
        $proxy = (new self(self::class))->createMock(Proxy::class);

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

        $resolver = new PropertyMappingResolver([], [], []);
        $factory = new PropertyMappingFactory($this->metadata, $resolver);
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

        $expectedFields = [
            'file' => [
                'mapping' => 'dummy_file',
                'propertyName' => 'file',
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
            ->willReturn($expectedFields);

        $resolver = new PropertyMappingResolver([], [], $mappings);
        $factory = new PropertyMappingFactory($this->metadata, $resolver);
        $mappings = $factory->fromObject($obj);

        self::assertCount(1, $mappings);

        $mapping = \current($mappings);
        $mapping->setNamer($this->createMock(NamerInterface::class));

        self::assertEquals('dummy_file', $mapping->getMappingName());
        self::assertEquals('images', $mapping->getUploadDestination());
        self::assertEquals('file_name', $mapping->getFileNamePropertyName());
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

        $expectedFields = [
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
            ->willReturn($expectedFields);

        $resolver = new PropertyMappingResolver([], [], $mappings);
        $factory = new PropertyMappingFactory($this->metadata, $resolver);
        $mappings = $factory->fromObject(new DummyEntity(), null, 'other_mapping');

        self::assertCount(1, $mappings);

        $mapping = \current($mappings);
        $mapping->setNamer($this->createMock(NamerInterface::class));

        self::assertEquals('other_mapping', $mapping->getMappingName());
    }

    /**
     * Test that an exception is thrown when an invalid mapping name
     * is specified.
     */
    public function testThrowsExceptionOnInvalidMappingName(): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\MappingNotFoundException::class);

        $mappings = ['bad_name' => []];
        $expectedFields = [
            'file' => [
                'mapping' => 'dummy_file',
                'propertyName' => 'file',
                'fileNameProperty' => 'fileName',
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
            ->willReturn($expectedFields);

        $resolver = new PropertyMappingResolver([], [], $mappings);
        $factory = new PropertyMappingFactory($this->metadata, $resolver);
        $factory->fromObject(new DummyEntity());
    }

    #[DataProvider('fromFieldProvider')]
    public function testFromField(object|array $object, ?string $className, string $expectedClassName): void
    {
        $mappings = [
            'dummy_file' => [
                'upload_destination' => 'images',
                'namer' => null,
                'directory_namer' => null,
            ],
        ];

        $expectedFields = [
            'mapping' => 'dummy_file',
            'propertyName' => 'file',
            'fileNameProperty' => 'fileName',
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
            ->willReturn($expectedFields);

        $resolver = new PropertyMappingResolver([], [], $mappings);
        $factory = new PropertyMappingFactory($this->metadata, $resolver);
        $mapping = $factory->fromField($object, 'file', $className);

        self::assertEquals('dummy_file', $mapping->getMappingName());
    }

    public static function fromFieldProvider(): array
    {
        $obj = new DummyEntity();
        $proxy = (new self(self::class))->createMock(Proxy::class);

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

        $resolver = new PropertyMappingResolver([], [], []);
        $factory = new PropertyMappingFactory($this->metadata, $resolver);
        $mapping = $factory->fromField(new DummyEntity(), 'oops');

        self::assertNull($mapping);
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
            ->willReturn(['mapping' => 'dummy_file', 'propertyName' => 'file']);

        $resolver = new PropertyMappingResolver([], [], $mappings, '_suffix');
        $factory = new PropertyMappingFactory($this->metadata, $resolver);
        $mapping = $factory->fromField(new DummyEntity(), 'file');

        self::assertEquals('file_suffix', $mapping->getFileNamePropertyName());
    }

    public function testConfiguredNamersAreRetrievedFromContainer(): void
    {
        $namer = $this->createStub(NamerInterface::class);
        $directoryNamer = $this->createStub(DirectoryNamerInterface::class);

        $mappings = [
            'dummy_file' => [
                'upload_destination' => 'images',
                'namer' => ['service' => $namer::class],
                'directory_namer' => ['service' => $directoryNamer::class],
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
            ->willReturn(['file' => ['mapping' => 'dummy_file', 'propertyName' => 'file', 'fileNameProperty' => 'fileName']]);

        $resolver = new PropertyMappingResolver(['id' => $namer], ['id' => $directoryNamer], $mappings);
        $factory = new PropertyMappingFactory($this->metadata, $resolver);
        $mappings = $factory->fromObject(new DummyEntity());

        self::assertCount(1, $mappings);

        $mapping = \current($mappings);
        $mapping->setNamer($namer);

        self::assertEquals($namer, $mapping->getNamer());
        self::assertTrue($mapping->hasNamer());
        self::assertEquals($directoryNamer, $mapping->getDirectoryNamer());
        self::assertTrue($mapping->hasDirectoryNamer());
    }
}
