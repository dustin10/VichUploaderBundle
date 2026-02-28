<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Mapping\Attribute\Uploadable;
use Vich\UploaderBundle\Mapping\Attribute\UploadableField;
use Vich\UploaderBundle\Metadata\Driver\AttributeReader;
use Vich\UploaderBundle\Tests\DummyAttributeEntity;

final class AttributeReaderTest extends TestCase
{
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
}
