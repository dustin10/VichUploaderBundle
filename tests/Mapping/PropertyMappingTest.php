<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Vich\TestBundle\Entity\Article;
use Vich\TestBundle\Entity\NotNullableArticle;
use Vich\TestBundle\Naming\DummyNamer;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\ConfigurableDirectoryNamer;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * PropertyMappingTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class PropertyMappingTest extends TestCase
{
    /**
     * Test that the configured mappings are accessed
     * correctly.
     */
    #[Test]
    public function configuredMappingAccess(): void
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');
        $prop->setMapping([
            'upload_destination' => '/tmp',
            'namer' => DummyNamer::class,
        ]);

        self::assertEquals('', $prop->getUploadDir($object));
        self::assertEquals('/tmp', $prop->getUploadDestination());
        self::assertEquals('file', $prop->getFilePropertyName());
        self::assertEquals('fileName', $prop->getFileNamePropertyName());
    }

    #[DataProvider('directoryProvider')]
    #[Test]
    public function directoryNamerIsCalled(string $dir, string $expectedDir): void
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');
        $prop->setMapping([
            'upload_destination' => '/tmp',
            'namer' => DummyNamer::class,
        ]);

        $namer = $this->createMock(DirectoryNamerInterface::class);
        $namer
            ->expects($this->once())
            ->method('directoryName')
            ->with($object, $prop)
            ->willReturn($dir);

        $prop->setDirectoryNamer($namer);

        self::assertEquals($expectedDir, $prop->getUploadDir($object));
        self::assertEquals('/tmp', $prop->getUploadDestination());
    }

    #[Test]
    public function readProperty(): void
    {
        $object = new DummyEntity();
        $object->setSize(100);
        $prop = new PropertyMapping('file', 'fileName', ['size' => 'size']);
        $prop->setMapping(['namer' => DummyNamer::class]);

        self::assertEquals(100, $prop->readProperty($object, 'size'));
    }

    #[Test]
    public function readUnknownProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');
        $prop->setMapping(['namer' => DummyNamer::class]);

        $prop->readProperty($object, 'unused');
    }

    #[Test]
    public function writeProperty(): void
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName', ['size' => 'size']);
        $prop->setMapping(['namer' => DummyNamer::class]);
        $prop->writeProperty($object, 'size', 100);

        self::assertEquals(100, $object->getSize());
    }

    #[Test]
    public function writeUnknownProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');
        $prop->setMapping(['namer' => DummyNamer::class]);

        $prop->writeProperty($object, 'unused', null);
    }

    #[Test]
    public function getUploadNameWithNamer(): void
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');

        $namer = $this->createMock(NamerInterface::class);
        $namer
            ->expects($this->once())
            ->method('name')
            ->with($object, $prop)
            ->willReturn('123');

        $prop->setNamer($namer);

        self::assertEquals('123', $prop->getUploadName($object));
    }

    public static function directoryProvider(): array
    {
        return [
            ['other_dir', 'other_dir'],
            ['other_dir/', 'other_dir'],
            ['other_dir\\', 'other_dir'],
            ['other_dir\\sub_dir', 'other_dir\\sub_dir'],
            ['other_dir\\sub_dir\\', 'other_dir\\sub_dir'],
        ];
    }

    #[Test]
    public function erase(): void
    {
        $object = new Article();

        $object->setImageName('generated.jpeg');
        $object->setOriginalNameField('original.jpeg');
        $object->setMimeTypeField('image/jpeg');
        $object->setSizeField('100');

        $prop = new PropertyMapping(
            'image',
            'imageName',
            [
                'size' => 'sizeField',
                'mimeType' => 'mimeTypeField',
                'originalName' => 'originalNameField',
                'namer' => DummyNamer::class,
            ]
        );

        $prop->erase($object);

        self::assertNull($object->getImageName());
        self::assertNull($object->getOriginalNameField());
        self::assertNull($object->getMimeTypeField());
        self::assertNull($object->getSizeField());
    }

    #[Test]
    public function eraseKeepsNonNullableProperties(): void
    {
        $object = new NotNullableArticle();
        $object->setImageName('generated.jpeg');
        $object->setSizeField('100');

        $prop = new PropertyMapping('image', 'imageName', ['size' => 'sizeField']);

        // The file name property is non-nullable: erasing must skip it (would raise a
        // \TypeError otherwise, see #1117) while still erasing the nullable size property.
        $prop->erase($object);

        self::assertSame('generated.jpeg', $object->getImageName());
        self::assertNull($object->getSizeField());
    }

    #[Test]
    public function isNullable(): void
    {
        $prop = new PropertyMapping('image', 'imageName', ['size' => 'sizeField']);

        // Nullable setter/property on Article.
        self::assertTrue($prop->isNullable(new Article(), 'name'));
        self::assertTrue($prop->isNullable(new Article(), 'size'));

        // Non-nullable file name, nullable size on NotNullableArticle.
        self::assertFalse($prop->isNullable(new NotNullableArticle(), 'name'));
        self::assertTrue($prop->isNullable(new NotNullableArticle(), 'size'));

        // Unconfigured property path is treated as nullable (write is a no-op).
        self::assertTrue($prop->isNullable(new Article(), 'mimeType'));
    }

    #[Test]
    public function isNullableMatchesTheAccessorWritePath(): void
    {
        // Snake_case path: the accessor camelizes it to setImageName(string), non-nullable.
        $snakeCased = new PropertyMapping('image', 'image_name');
        self::assertFalse($snakeCased->isNullable(new NotNullableArticle(), 'name'));

        // Non-nullable public property written directly (no setter).
        $publicProperty = new PropertyMapping('image', 'imageName', ['size' => 'publicSize']);
        self::assertFalse($publicProperty->isNullable(new NotNullableArticle(), 'size'));
    }

    #[Test]
    public function isNullableRejectsUnknownMappingProperty(): void
    {
        $prop = new PropertyMapping('image', 'imageName');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown property unused');

        $prop->isNullable(new Article(), 'unused');
    }

    #[Test]
    public function isNullableTreatsNonTypedWriteTargetsAsNullable(): void
    {
        $arrayOffset = new PropertyMapping('image', '[imageName]');
        self::assertTrue($arrayOffset->isNullable(new \ArrayObject(), 'name'));

        $nestedTarget = new class() {
            public ?object $metadata = null;
        };
        $nestedProperty = new PropertyMapping('image', 'metadata.imageName');
        self::assertTrue($nestedProperty->isNullable($nestedTarget, 'name'));

        $dynamicProperty = new PropertyMapping('image', 'imageName');
        self::assertTrue($dynamicProperty->isNullable(new \stdClass(), 'name'));
    }

    #[Test]
    public function withArray(): void
    {
        $prop = new PropertyMapping(
            'image',
            'imageName',
        );

        $directoryNamer = new ConfigurableDirectoryNamer();
        $directoryNamer->configure([
            'directory_path' => 'fake',
        ]);
        $prop->setDirectoryNamer($directoryNamer);

        $object = [];
        $actual = $prop->getUploadDir($object);
        self::assertEquals('fake', $actual);
    }
}
