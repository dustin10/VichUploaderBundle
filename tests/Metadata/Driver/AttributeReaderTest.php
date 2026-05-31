<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable as DeprecatedUploadable;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField as DeprecatedUploadableField;
use Vich\UploaderBundle\Mapping\AnnotationInterface;
use Vich\UploaderBundle\Mapping\Attribute\Uploadable;
use Vich\UploaderBundle\Mapping\Attribute\UploadableField;
use Vich\UploaderBundle\Metadata\Driver\AttributeReader;
use Vich\UploaderBundle\Tests\DummyAttributeEntity;

final class AttributeReaderTest extends TestCase
{
    /**
     * @group legacy
     */
    public function testGetClassAttributes(): void
    {
        $reader = new AttributeReader();
        $class = new \ReflectionClass(DummyAttributeEntity::class);

        $this->assertEquals(
            [
                Uploadable::class => new Uploadable(),
            ],
            $reader->getClassAttributes($class)
        );
    }

    /**
     * @group legacy
     */
    public function testGetClassAttribute(): void
    {
        $reader = new AttributeReader();
        $class = new \ReflectionClass(DummyAttributeEntity::class);

        $this->assertEquals(
            new Uploadable(),
            $reader->getClassAttribute($class, Uploadable::class)
        );

        $this->assertNull(
            $reader->getClassAttribute($class, self::class)
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

    /**
     * @group legacy
     */
    public function testGetPropertyAttributes(): void
    {
        $reader = new AttributeReader();
        $class = new \ReflectionProperty(DummyAttributeEntity::class, 'file');

        $this->assertEquals(
            [
                UploadableField::class => new UploadableField('dummy_file', 'fileName'),
            ],
            $reader->getPropertyAttributes($class)
        );
    }

    /**
     * @group legacy
     */
    public function testGetPropertyAttribute(): void
    {
        $reader = new AttributeReader();
        $class = new \ReflectionProperty(DummyAttributeEntity::class, 'file');

        $this->assertEquals(
            new UploadableField('dummy_file', 'fileName'),
            $reader->getPropertyAttribute($class, UploadableField::class)
        );

        $this->assertNull(
            $reader->getPropertyAttribute(
                new \ReflectionProperty(DummyAttributeEntity::class, 'someProperty'),
                UploadableField::class
            )
        );
    }

    /**
     * @group legacy
     */
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

    /**
     * @group legacy
     */
    public function testDeprecatedAnnotationClassesImplementCompatibilityInterface(): void
    {
        $uploadable = new DeprecatedUploadable();
        $uploadableField = new DeprecatedUploadableField('dummy_file', 'fileName');

        $this->assertInstanceOf(AnnotationInterface::class, $uploadable);
        $this->assertInstanceOf(AnnotationInterface::class, $uploadableField);
        $this->assertSame('dummy_file', $uploadableField->getMapping());
        $this->assertSame('fileName', $uploadableField->getFileNameProperty());
    }
}
