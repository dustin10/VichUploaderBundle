<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use Vich\TestBundle\Entity\Article;
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
        ]);

        $this->assertEquals('', $prop->getUploadDir($object));
        $this->assertEquals('/tmp', $prop->getUploadDestination());
        $this->assertEquals('file', $prop->getFilePropertyName());
        $this->assertEquals('fileName', $prop->getFileNamePropertyName());
    }

    /**
     * @dataProvider directoryProvider
     */
    public function testDirectoryNamerIsCalled($dir, $expectedDir): void
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');
        $prop->setMapping([
            'upload_destination' => '/tmp',
        ]);

        $namer = $this->createMock(DirectoryNamerInterface::class);
        $namer
            ->expects($this->once())
            ->method('directoryName')
            ->with($object, $prop)
            ->willReturn($dir);

        $prop->setDirectoryNamer($namer);

        $this->assertEquals($expectedDir, $prop->getUploadDir($object));
        $this->assertEquals('/tmp', $prop->getUploadDestination());
    }

    public function testReadProperty(): void
    {
        $object = new DummyEntity();
        $object->setSize(100);
        $prop = new PropertyMapping('file', 'fileName', ['size' => 'size']);

        $this->assertEquals(100, $prop->readProperty($object, 'size'));
    }

    public function testReadUnknownProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');

        $prop->readProperty($object, 'unused');
    }

    public function testWriteProperty(): void
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName', ['size' => 'size']);
        $prop->writeProperty($object, 'size', 100);

        $this->assertEquals(100, $object->getSize());
    }

    public function testWriteUnknownProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');

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

        $this->assertEquals('123', $prop->getUploadName($object));
    }

    public function testGetUploadNameWithoutNamer(): void
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');

        $file = $this->getUploadedFileMock();
        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn('filename');

        $object->setFile($file);

        $this->assertEquals('filename', $prop->getUploadName($object));
    }

    public function directoryProvider(): array
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
        $object->setSizeField(100);

        $prop = new PropertyMapping(
            'image',
            'imageName',
            [
                'size' => 'sizeField',
                'mimeType' => 'mimeTypeField',
                'originalName' => 'originalNameField',
            ]
        );

        $prop->erase($object);

        $this->assertNull($object->getImageName());
        $this->assertNull($object->getOriginalNameField());
        $this->assertNull($object->getMimeTypeField());
        $this->assertNull($object->getSizeField());
    }
}
