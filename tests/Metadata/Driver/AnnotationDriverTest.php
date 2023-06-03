<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Doctrine\Common\Annotations\Reader;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Metadata\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Vich\TestBundle\Entity\Article;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Vich\UploaderBundle\Metadata\Driver\AnnotationDriver;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\DummyFile;
use Yoast\PHPUnitPolyfills\Polyfills\AssertObjectProperty;

/**
 * AnnotationDriverTest.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
final class AnnotationDriverTest extends TestCase
{
    use AssertObjectProperty;

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

    public function testReadUploadableAnnotation(): void
    {
        $entity = new DummyEntity();

        $reader = $this->createMock(Reader::class);
        $reader
            ->expects(self::once())
            ->method('getClassAnnotation')
            ->willReturn('something not null');
        $reader
            ->expects(self::atLeastOnce())
            ->method('getPropertyAnnotation')
            ->willReturnCallback(static fn (ReflectionProperty $property): ?UploadableField => 'file' === $property->getName() ? new UploadableField('dummy_file', 'fileName') : null);

        $driver = new AnnotationDriver($reader, [$this->managerRegistry]);
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

    public function testReadUploadableAnnotationReturnsNullWhenNonePresent(): void
    {
        $entity = new DummyEntity();

        $reader = $this->createMock(Reader::class);
        $reader
            ->expects(self::once())
            ->method('getClassAnnotation')
            ->willReturn(null);
        $reader
            ->expects($this->never())
            ->method('getPropertyAnnotation');

        $driver = new AnnotationDriver($reader, [$this->managerRegistry]);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        self::assertNull($metadata);
    }

    public function testReadTwoUploadableFields(): void
    {
        $entity = new Article();

        $reader = $this->createMock(Reader::class);
        $reader
            ->expects(self::once())
            ->method('getClassAnnotation')
            ->willReturn('something not null');

        $reader
            ->expects(self::atLeast(2))
            ->method('getPropertyAnnotation')
            ->willReturnCallback(static function (ReflectionProperty $property): ?UploadableField {
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

        $driver = new AnnotationDriver($reader, [$this->managerRegistry]);
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

        $reader = $this->createMock(Reader::class);
        $reader
            ->expects(self::once())
            ->method('getClassAnnotation')
            ->willReturn('something not null');

        $driver = new AnnotationDriver($reader, [$this->managerRegistry]);
        /** @var \Vich\UploaderBundle\Metadata\ClassMetadata $metadata */
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        self::assertEmpty($metadata->fields);
    }

    public function testReadUploadableAnnotationInParentClass(): void
    {
        $entity = new DummyFile();

        $reader = $this->createMock(Reader::class);
        $reader
            ->expects(self::once())
            ->method('getClassAnnotation')
            ->willReturn('something not null');
        $reader
            ->expects(self::atLeastOnce())
            ->method('getPropertyAnnotation')
            ->willReturnCallback(static fn (ReflectionProperty $property): ?UploadableField => 'file' === $property->getName() ? new UploadableField('dummyFile_file', 'fileName') : null);

        $driver = new AnnotationDriver($reader, [$this->managerRegistry]);
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

    public function testReadUploadableAnnotationReturnsNullWhenNonePresentInParentClass(): void
    {
        $entity = new DummyFile();

        $reader = $this->createMock(Reader::class);
        $reader
            ->expects(self::once())
            ->method('getClassAnnotation')
            ->willReturn(null);
        $reader
            ->expects($this->never())
            ->method('getPropertyAnnotation');

        $driver = new AnnotationDriver($reader, [$this->managerRegistry]);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        self::assertNull($metadata);
    }
}
