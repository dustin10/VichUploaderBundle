<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use Vich\TestBundle\Entity\Article;
use Vich\TestBundle\Naming\DummyNamer;
use Vich\UploaderBundle\Mapping\PropertyMapping;
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

    /**
     * @dataProvider directoryProvider
     */
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
            ->expects(self::once())
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
            ->expects(self::once())
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
}
