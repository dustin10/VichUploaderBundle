<?php

namespace Metadata\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Metadata\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vich\TestBundle\Entity\Article;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable as UploadableAnnotation;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField as UploadableFieldAnnotation;
use Vich\UploaderBundle\Mapping\Attribute\Uploadable;
use Vich\UploaderBundle\Mapping\Attribute\UploadableField;
use Vich\UploaderBundle\Metadata\Driver\AttributeDriver;
use Vich\UploaderBundle\Metadata\Driver\AttributeReader;
use Vich\UploaderBundle\Tests\DummyAttributeEntity;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\DummyFile;

/**
 * @author Andy Palmer <andy@andypalmer.me>
 */
final class AttributeDriverTest extends TestCase
{
    private Connection|MockObject $connection;

    private EntityManagerInterface|MockObject $entityManager;

    private ManagerRegistry|MockObject $managerRegistry;

    protected function setUp(): void
    {
        // setup ManagerRegistry mock like Symfony\Bridge\Doctrine tests
        $this->connection = $this->createMock(Connection::class);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager->method('getConnection')->willReturn($this->connection);

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->managerRegistry->method('getManager')->willReturn($this->entityManager);
    }

    public function testReadUploadableAttribute(): void
    {
        $entity = new DummyEntity();

        // @phpstan-ignore-next-line method.unresolvableReturnType
        $reader = $this->createMock(AttributeReader::class);
        $reader
            ->expects(self::once())
            ->method('getClassAttribute')
            ->willReturn(new Uploadable());
        $reader
            ->expects(self::atLeastOnce())
            ->method('getPropertyAttribute')
            ->willReturnCallback(static fn (\ReflectionProperty $property): ?UploadableField => 'file' === $property->getName() ? new UploadableField('dummy_file', 'fileName') : null);

        $driver = new AttributeDriver($reader, [$this->managerRegistry]);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        self::assertInstanceOf(\Vich\UploaderBundle\Metadata\ClassMetadata::class, $metadata);
        self::assertObjectHasProperty('fields', $metadata);
        self::assertEquals([
            'file' => [
                'mapping' => 'dummy_file',
                'propertyName' => 'file',
                'fileNameProperty' => 'fileName',
                'size' => null,
                'mimeType' => null,
                'originalName' => null,
                'dimensions' => null,
            ],
        ], $metadata->fields);
    }

    public function testReadUploadableAnnotation(): void
    {
        $entity = new DummyAttributeEntity();

        // @phpstan-ignore-next-line method.unresolvableReturnType
        $reader = $this->createMock(AttributeReader::class);
        $reader
            ->expects(self::once())
            ->method('getClassAttribute')
            ->willReturn(new UploadableAnnotation());
        $reader
            ->expects(self::atLeastOnce())
            ->method('getPropertyAttribute')
            ->willReturnCallback(static fn (\ReflectionProperty $property): ?UploadableFieldAnnotation => 'file' === $property->getName() ? new UploadableFieldAnnotation('dummy_file', 'fileName') : null);

        $driver = new AttributeDriver($reader, [$this->managerRegistry]);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        self::assertInstanceOf(\Vich\UploaderBundle\Metadata\ClassMetadata::class, $metadata);
        self::assertObjectHasProperty('fields', $metadata);
        self::assertEquals([
            'file' => [
                'mapping' => 'dummy_file',
                'propertyName' => 'file',
                'fileNameProperty' => 'fileName',
                'size' => null,
                'mimeType' => null,
                'originalName' => null,
                'dimensions' => null,
            ],
        ], $metadata->fields);
    }

    public function testReadUploadableAttributeReturnsNullWhenNonePresent(): void
    {
        $entity = new DummyEntity();

        // @phpstan-ignore-next-line method.unresolvableReturnType
        $reader = $this->createMock(AttributeReader::class);
        $reader
            ->expects(self::exactly(2))
            ->method('getClassAttribute')
            ->willReturn(null);
        $reader
            ->expects(self::never())
            ->method('getPropertyAttribute');

        $driver = new AttributeDriver($reader, [$this->managerRegistry]);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        self::assertNull($metadata);
    }

    public function testReadTwoUploadableFields(): void
    {
        $entity = new Article();

        // @phpstan-ignore-next-line method.unresolvableReturnType
        $reader = $this->createMock(AttributeReader::class);
        $reader
            ->expects(self::once())
            ->method('getClassAttribute')
            ->willReturn(new Uploadable());

        $reader
            ->expects(self::atLeast(2))
            ->method('getPropertyAttribute')
            ->willReturnCallback(static function (\ReflectionProperty $property): ?UploadableField {
                if ('attachment' === $property->getName()) {
                    return new UploadableField('dummy_file', 'attachmentName');
                }

                if ('image' === $property->getName()) {
                    return new UploadableField(
                        'dummy_image',
                        'imageName',
                        'sizeField',
                        'mimeTypeField',
                        'originalNameField'
                    );
                }

                return null;
            });

        $driver = new AttributeDriver($reader, [$this->managerRegistry]);
        /** @var \Vich\UploaderBundle\Metadata\ClassMetadata $metadata */
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        self::assertEquals([
            'attachment' => [
                'mapping' => 'dummy_file',
                'propertyName' => 'attachment',
                'fileNameProperty' => 'attachmentName',
                'size' => null,
                'mimeType' => null,
                'originalName' => null,
                'dimensions' => null,
            ],
            'image' => [
                'mapping' => 'dummy_image',
                'propertyName' => 'image',
                'fileNameProperty' => 'imageName',
                'size' => 'sizeField',
                'mimeType' => 'mimeTypeField',
                'originalName' => 'originalNameField',
                'dimensions' => null,
            ],
        ], $metadata->fields);
    }

    public function testReadNoUploadableFieldsWhenNoneExist(): void
    {
        $entity = new DummyEntity();

        // @phpstan-ignore-next-line method.unresolvableReturnType
        $reader = $this->createMock(AttributeReader::class);
        $reader
            ->expects(self::once())
            ->method('getClassAttribute')
            ->willReturn(new Uploadable());

        $driver = new AttributeDriver($reader, [$this->managerRegistry]);
        /** @var \Vich\UploaderBundle\Metadata\ClassMetadata $metadata */
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        self::assertEmpty($metadata->fields);
    }

    public function testReadUploadableAttributeInParentClass(): void
    {
        $entity = new DummyFile();

        // @phpstan-ignore-next-line method.unresolvableReturnType
        $reader = $this->createMock(AttributeReader::class);
        $reader
            ->expects(self::once())
            ->method('getClassAttribute')
            ->willReturn(new Uploadable());
        $reader
            ->expects(self::atLeastOnce())
            ->method('getPropertyAttribute')
            ->willReturnCallback(static fn (\ReflectionProperty $property): ?UploadableField => 'file' === $property->getName() ? new UploadableField('dummyFile_file', 'fileName') : null);

        $driver = new AttributeDriver($reader, [$this->managerRegistry]);
        /** @var \Vich\UploaderBundle\Metadata\ClassMetadata $metadata */
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        self::assertInstanceOf(ClassMetadata::class, $metadata);
        self::assertObjectHasProperty('fields', $metadata);
        self::assertEquals(
            [
                'file' => [
                    'mapping' => 'dummyFile_file',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                    'size' => null,
                    'mimeType' => null,
                    'originalName' => null,
                    'dimensions' => null,
                ],
            ],
            $metadata->fields
        );
    }

    public function testReadUploadableAttributeReturnsNullWhenNonePresentInParentClass(): void
    {
        $entity = new DummyFile();

        // @phpstan-ignore-next-line method.unresolvableReturnType
        $reader = $this->createMock(AttributeReader::class);
        $reader
            ->expects(self::exactly(2))
            ->method('getClassAttribute')
            ->willReturn(null);
        $reader
            ->expects($this->never())
            ->method('getPropertyAttribute');

        $driver = new AttributeDriver($reader, [$this->managerRegistry]);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        self::assertNull($metadata);
    }
}
