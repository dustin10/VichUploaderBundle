<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Vich\UploaderBundle\Metadata\Driver\AttributeReader;
use Vich\UploaderBundle\Tests\DummyAttributeEntity;

final class AttributeReaderTest extends TestCase
{
    public function testGetClassAnnotations(): void
    {
        $reader = new AttributeReader();
        $class = new \ReflectionClass(DummyAttributeEntity::class);

        $this->assertEquals(
            [
                Uploadable::class => new Uploadable(),
            ],
            $reader->getClassAnnotations($class)
        );
    }

    public function testGetClassAnnotation(): void
    {
        $reader = new AttributeReader();
        $class = new \ReflectionClass(DummyAttributeEntity::class);

        $this->assertEquals(
            new Uploadable(),
            $reader->getClassAnnotation($class, Uploadable::class)
        );

        $this->assertNull(
            $reader->getClassAnnotation($class, self::class)
        );
    }

    public function testGetPropertyAnnotations(): void
    {
        $reader = new AttributeReader();
        $class = new \ReflectionProperty(DummyAttributeEntity::class, 'file');

        $this->assertEquals(
            [
                UploadableField::class => new UploadableField('dummy_file', 'fileName'),
            ],
            $reader->getPropertyAnnotations($class)
        );
    }

    public function testGetPropertyAnnotation(): void
    {
        $reader = new AttributeReader();
        $class = new \ReflectionProperty(DummyAttributeEntity::class, 'file');

        $this->assertEquals(
            new UploadableField('dummy_file', 'fileName'),
            $reader->getPropertyAnnotation($class, UploadableField::class)
        );

        $this->assertNull(
            $reader->getPropertyAnnotation(
                new \ReflectionProperty(DummyAttributeEntity::class, 'someProperty'),
                UploadableField::class
            )
        );
    }
}
