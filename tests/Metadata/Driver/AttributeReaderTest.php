<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Mapping\Attribute\Uploadable;
use Vich\UploaderBundle\Mapping\Attribute\UploadableField;
use Vich\UploaderBundle\Metadata\Driver\AttributeReader;
use Vich\UploaderBundle\Tests\DummyAttributeEntity;

final class AttributeReaderTest extends TestCase
{
    #[Test]
    public function getClassAttributes(): void
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

    #[Test]
    public function getClassAttribute(): void
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

    #[Test]
    public function getPropertyAttributes(): void
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

    #[Test]
    public function getPropertyAttribute(): void
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

    #[Test]
    public function doesNotInstantiateUnrelatedAttributes(): void
    {
        $reader = new AttributeReader();
        $class = new \ReflectionClass(EntityWithUnrelatedAttribute::class);

        $this->assertEquals(
            [Uploadable::class => new Uploadable()],
            $reader->getClassAttributes($class)
        );
    }
}

#[Uploadable]
#[UnrelatedAttribute]
final class EntityWithUnrelatedAttribute
{
}

#[\Attribute]
final class UnrelatedAttribute
{
    public function __construct()
    {
        throw new \LogicException();
    }
}
