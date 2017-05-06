<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use Vich\TestBundle\Entity\Article;
use Vich\UploaderBundle\Mapping\PropertyMapping;
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
    public function testConfiguredMappingAccess()
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
     * @dataProvider propertiesAccessProvider
     */
    public function testPropertiesAreAccessed($object, $file, $fileName)
    {
        $prop = new PropertyMapping('file', 'fileName');

        $this->assertSame($file, $prop->getFile($object));
        $this->assertSame($fileName, $prop->getFileName($object));
    }

    public function propertiesAccessProvider()
    {
        $date = new \DateTime();
        $object = new DummyEntity();
        $object->setFileName('joe.png');
        $object->setFile($date);

        $array = [
            'fileName' => 'joe.png',
            'file' => $date,
        ];

        return [
            [$object, $date, 'joe.png'],
            [$array, $date, 'joe.png'],
        ];
    }

    public function testPropertiesAreSet()
    {
        $date = new \DateTime();
        $object = new DummyEntity();

        $prop = new PropertyMapping('file', 'fileName');
        $prop->setFile($object, $date);
        $prop->setFileName($object, 'joe.png');

        $this->assertSame($date, $object->getFile());
        $this->assertSame('joe.png', $object->getFileName());
    }

    /**
     * @dataProvider directoryProvider
     */
    public function testDirectoryNamerIsCalled($dir, $expectedDir)
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');
        $prop->setMapping([
            'upload_destination' => '/tmp',
        ]);

        $namer = $this->createMock('Vich\UploaderBundle\Naming\DirectoryNamerInterface');
        $namer
            ->expects($this->once())
            ->method('directoryName')
            ->with($object, $prop)
            ->will($this->returnValue($dir));

        $prop->setDirectoryNamer($namer);

        $this->assertEquals($expectedDir, $prop->getUploadDir($object));
        $this->assertEquals('/tmp', $prop->getUploadDestination());
    }

    public function testReadProperty()
    {
        $object = new DummyEntity();
        $object->setSize(100);
        $prop = new PropertyMapping('file', 'fileName', ['size' => 'size']);

        $this->assertEquals(100, $prop->readProperty($object, 'size'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReadUnknownProperty()
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');

        $prop->readProperty($object, 'unused');
    }

    public function testWriteProperty()
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName', ['size' => 'size']);
        $prop->writeProperty($object, 'size', 100);

        $this->assertEquals(100, $object->getSize());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWriteUnknownProperty()
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');

        $prop->writeProperty($object, 'unused', null);
    }

    public function testGetUploadNameWithNamer()
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');

        $namer = $this->createMock(NamerInterface::class);
        $namer
            ->expects($this->once())
            ->method('name')
            ->with($object, $prop)
            ->will($this->returnValue('123'));

        $prop->setNamer($namer);

        $this->assertEquals('123', $prop->getUploadName($object));
    }

    public function testGetUploadNameWithoutNamer()
    {
        $object = new DummyEntity();
        $prop = new PropertyMapping('file', 'fileName');

        $file = $this->getUploadedFileMock();
        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('filename'));

        $object->setFile($file);

        $this->assertEquals('filename', $prop->getUploadName($object));
    }

    public function directoryProvider()
    {
        return [
            ['other_dir', 'other_dir'],
            ['other_dir/', 'other_dir'],
            ['other_dir\\', 'other_dir'],
            ['other_dir\\sub_dir', 'other_dir\\sub_dir'],
            ['other_dir\\sub_dir\\', 'other_dir\\sub_dir'],
        ];
    }

    public function testErase()
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
