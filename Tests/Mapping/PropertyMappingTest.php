<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * PropertyMappingTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class PropertyMappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the configured mappings are accessed
     * correctly.
     */
    public function testConfiguredMappingAccess()
    {
        $prop = new PropertyMapping('file', 'fileName');
        $prop->setMapping(array(
            'upload_destination'    => '/tmp',
            'delete_on_remove'      => true,
            'delete_on_update'      => true,
            'inject_on_load'        => false,
        ));

        $this->assertEquals('/tmp', $prop->getUploadDir());
        $this->assertEquals('file', $prop->getFilePropertyName());
        $this->assertEquals('fileName', $prop->getFileNamePropertyName());
        $this->assertTrue($prop->getDeleteOnRemove());
        $this->assertTrue($prop->getDeleteOnUpdate());
        $this->assertFalse($prop->getInjectOnLoad());
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

        $array = array(
            'fileName'  => 'joe.png',
            'file'      => $date,
        );

        return array(
            array( $object, $date, 'joe.png' ),
            array( $array,  $date, 'joe.png' ),
        );
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

    public function testDirectoryNamerIsCalled()
    {
        $prop = new PropertyMapping('file', 'fileName');
        $prop->setMapping(array(
            'upload_destination' => '/tmp',
        ));

        $namer = $this->getMock('Vich\UploaderBundle\Naming\DirectoryNamerInterface');
        $namer
            ->expects($this->once())
            ->method('directoryName')
            ->with(null, $prop)
            ->will($this->returnValue('/other-dir'));

        $prop->setDirectoryNamer($namer);

        $this->assertEquals('/other-dir', $prop->getUploadDir());
        $this->assertEquals('/tmp', $prop->getUploadDestination());
    }
}
