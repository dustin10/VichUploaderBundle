<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Vich\UploaderBundle\Mapping\Attribute\Uploadable as AttributeUploadable;
use Vich\UploaderBundle\Mapping\Attribute\UploadableField as AttributeUploadableField;
use Vich\UploaderBundle\Metadata\Driver\AttributeReader;
use Vich\UploaderBundle\Tests\DummyAttributeEntity;
use Vich\UploaderBundle\Tests\DummyNewAttributeEntity;

final class AttributeReaderTest extends TestCase
{
    /**
     * @group legacy
     */
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

    /**
     * @group legacy
     */
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
     * Test new Attribute namespace methods work correctly.
     */
    public function testGetClassAttributeWithNewNamespace(): void
    {
        $reader = new AttributeReader();
        $class = new \ReflectionClass(DummyNewAttributeEntity::class);

        $this->assertEquals(
            new AttributeUploadable(),
            $reader->getClassAttribute($class, AttributeUploadable::class)
        );

        $this->assertNull(
            $reader->getClassAttribute($class, self::class)
        );
    }

    /**
     * Test new Attribute namespace methods work correctly.
     */
    public function testGetPropertyAttributeWithNewNamespace(): void
    {
        $reader = new AttributeReader();
        $property = new \ReflectionProperty(DummyNewAttributeEntity::class, 'file');

        $this->assertEquals(
            new AttributeUploadableField('dummy_file', 'fileName'),
            $reader->getPropertyAttribute($property, AttributeUploadableField::class)
        );

        $this->assertNull(
            $reader->getPropertyAttribute(
                new \ReflectionProperty(DummyNewAttributeEntity::class, 'someProperty'),
                AttributeUploadableField::class
            )
        );
    }

    /**
     * Test that deprecated Annotation namespace still works (BC test).
     *
     * @group legacy
     */
    public function testGetClassAttributeWithDeprecatedNamespace(): void
    {
        $reader = new AttributeReader();
        $class = new \ReflectionClass(DummyAttributeEntity::class);

        // Should work with both old Annotation namespace and new Attribute methods
        $this->assertEquals(
            new Uploadable(),
            $reader->getClassAttribute($class, Uploadable::class)
        );
    }

    /**
     * Test that deprecated Annotation namespace still works (BC test).
     *
     * @group legacy
     */
    public function testGetPropertyAttributeWithDeprecatedNamespace(): void
    {
        $reader = new AttributeReader();
        $property = new \ReflectionProperty(DummyAttributeEntity::class, 'file');

        // Should work with both old Annotation namespace and new Attribute methods
        $this->assertEquals(
            new UploadableField('dummy_file', 'fileName'),
            $reader->getPropertyAttribute($property, UploadableField::class)
        );
    }
}
