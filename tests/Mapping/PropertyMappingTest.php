<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use PHPUnit\Framework\Attributes\DataProvider;
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
    public function testConfiguredMappingAccess(): void
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
    public function testDirectoryNamerIsCalled(string $dir, string $expectedDir): void
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

    public function testReadProperty(): void
    {
        $object = new DummyEntity();
        $object->setSize(100);
        $prop = new PropertyMapping('file', 'fileName', ['size' => 'size']);
        $prop->setMapping(['namer' => DummyNamer::class]);

        self::assertEquals(100, $prop->readProperty($object, 'size'));
    }

    public function testReadUnknownProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');
        $prop->setMapping(['namer' => DummyNamer::class]);

        $prop->readProperty($object, 'unused');
    }

    public function testWriteProperty(): void
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName', ['size' => 'size']);
        $prop->setMapping(['namer' => DummyNamer::class]);
        $prop->writeProperty($object, 'size', 100);

        self::assertEquals(100, $object->getSize());
    }

    public function testWriteUnknownProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');
        $prop->setMapping(['namer' => DummyNamer::class]);

        $prop->writeProperty($object, 'unused', null);
    }

    public function testGetUploadNameWithNamer(): void
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

    public function testErase(): void
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

    public function testEraseKeepsNonNullableProperties(): void
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

    public function testIsNullable(): void
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

    public function testIsNullableMatchesTheAccessorWritePath(): void
    {
        // Snake_case path: the accessor camelizes it to setImageName(string), non-nullable.
        $snakeCased = new PropertyMapping('image', 'image_name');
        self::assertFalse($snakeCased->isNullable(new NotNullableArticle(), 'name'));

        // Non-nullable public property written directly (no setter).
        $publicProperty = new PropertyMapping('image', 'imageName', ['size' => 'publicSize']);
        self::assertFalse($publicProperty->isNullable(new NotNullableArticle(), 'size'));
    }

    public function testIsNullableRejectsUnknownMappingProperty(): void
    {
        $prop = new PropertyMapping('image', 'imageName');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown property unused');

        $prop->isNullable(new Article(), 'unused');
    }

    public function testIsNullableTreatsNonTypedWriteTargetsAsNullable(): void
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

    public function testWithArray(): void
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
