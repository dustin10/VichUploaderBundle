<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use PHPUnit\Framework\TestCase;
use Vich\TestBundle\Entity\Article;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Vich\UploaderBundle\Metadata\Driver\AnnotationDriver;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * AnnotationDriverTest.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class AnnotationDriverTest extends TestCase
{
    public function testReadUploadableAnnotation()
    {
        $entity = new DummyEntity();

        $reader = $this->createMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue('something not null'));
        $reader
            ->expects($this->at(1))
            ->method('getPropertyAnnotation')
            ->will($this->returnValue(new UploadableField([
                'mapping' => 'dummy_file',
                'fileNameProperty' => 'fileName',
            ])));

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertInstanceOf('\Vich\UploaderBundle\Metadata\ClassMetadata', $metadata);
        $this->assertObjectHasAttribute('fields', $metadata);
        $this->assertEquals([
            'file' => [
                'mapping' => 'dummy_file',
                'propertyName' => 'file',
                'fileNameProperty' => 'fileName',
                'size' => null,
                'mimeType' => null,
                'originalName' => null,
            ],
        ], $metadata->fields);
    }

    public function testReadUploadableAnnotationReturnsNullWhenNonePresent()
    {
        $entity = new DummyEntity();

        $reader = $this->createMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue(null));
        $reader
            ->expects($this->never())
            ->method('getPropertyAnnotation');

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertNull($metadata);
    }

    public function testReadTwoUploadableFields()
    {
        $entity = new Article();

        $reader = $this->createMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue('something not null'));
        $reader
            ->expects($this->at(1))
            ->method('getPropertyAnnotation')
            ->will($this->returnValue(new UploadableField([
                'mapping' => 'dummy_file',
                'fileNameProperty' => 'attachmentName',
            ])));
        $reader
            ->expects($this->at(3))
            ->method('getPropertyAnnotation')
            ->will($this->returnValue(new UploadableField([
                'mapping' => 'dummy_image',
                'fileNameProperty' => 'imageName',
                'size' => 'sizeField',
                'mimeType' => 'mimeTypeField',
                'originalName' => 'originalNameField',
            ])));

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
            ],
            'image' => [
                'mapping' => 'dummy_image',
                'propertyName' => 'image',
                'fileNameProperty' => 'imageName',
                'size' => 'sizeField',
                'mimeType' => 'mimeTypeField',
                'originalName' => 'originalNameField',
            ],
        ], $metadata->fields);
    }

    public function testReadNoUploadableFieldsWhenNoneExist()
    {
        $entity = new DummyEntity();

        $reader = $this->createMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue('something not null'));

        $driver = new AnnotationDriver($reader);
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass($entity));

        $this->assertEmpty($metadata->fields);
    }
}
