<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Doctrine\Common\Annotations\Reader;
use Metadata\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Vich\TestBundle\Entity\Article;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Vich\UploaderBundle\Metadata\Driver\AnnotationDriver;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\DummyFile;

/**
 * AnnotationDriverTest.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class AnnotationDriverTest extends TestCase
{
    public function testReadUploadableAnnotation(): void
    {
        $entity = new DummyEntity();

        $reader = $this->createMock(Reader::class);
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->willReturn('something not null');
        $reader
            ->expects($this->at(1))
            ->method('getPropertyAnnotation')
            ->willReturn(new UploadableField([
                'mapping' => 'dummy_file',
                'fileNameProperty' => 'fileName',
            ]));

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertObjectHasAttribute('fields', $metadata);
        $this->assertEquals([
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
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->willReturn(null);
        $reader
            ->expects($this->never())
            ->method('getPropertyAnnotation');

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertNull($metadata);
    }

    public function testReadTwoUploadableFields(): void
    {
        $entity = new Article();

        $reader = $this->createMock(Reader::class);
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->willReturn('something not null');
        $reader
            ->expects($this->at(1))
            ->method('getPropertyAnnotation')
            ->willReturn(new UploadableField([
                'mapping' => 'dummy_file',
                'fileNameProperty' => 'attachmentName',
            ]));
        $reader
            ->expects($this->at(3))
            ->method('getPropertyAnnotation')
            ->willReturn(new UploadableField([
                'mapping' => 'dummy_image',
                'fileNameProperty' => 'imageName',
                'size' => 'sizeField',
                'mimeType' => 'mimeTypeField',
                'originalName' => 'originalNameField',
                'dimensions' => null,
            ]));

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertEquals([
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
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->willReturn('something not null');

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertEmpty($metadata->fields);
    }

    public function testReadUploadableAnnotationInParentClass(): void
    {
        $entity = new DummyFile();

        $reader = $this->createMock(Reader::class);
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->willReturn('something not null');
        $reader
            ->expects($this->at(4))
            ->method('getPropertyAnnotation')
            ->willReturn(
                    new UploadableField(
                        [
                            'mapping' => 'dummyFile_file',
                            'fileNameProperty' => 'fileName',
                        ]
                    )
            );

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertObjectHasAttribute('fields', $metadata);
        $this->assertEquals(
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
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->willReturn(null);
        $reader
            ->expects($this->never())
            ->method('getPropertyAnnotation');

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertNull($metadata);
    }
}
