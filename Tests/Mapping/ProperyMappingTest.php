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
        $this->assertTrue($prop->getDeleteOnRemove());
        $this->assertTrue($prop->getDeleteOnUpdate());
        $this->assertFalse($prop->getInjectOnLoad());
    }

    public function testPropertiesAreAccessed()
    {
        $date = new \DateTime();
        $object = new DummyEntity();
        $object->setFileName('joe.png');
        $object->setFile($date);

        $prop = new PropertyMapping('file', 'fileName');

        $this->assertSame($date, $prop->getFile($object));
        $this->assertSame('joe.png', $prop->getFileName($object));
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
}
