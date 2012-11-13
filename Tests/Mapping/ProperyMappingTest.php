<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use Vich\UploaderBundle\Mapping\PropertyMapping;

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
        $prop = new PropertyMapping();
        $prop->setMapping(array(
            'delete_on_remove' => true,
            'delete_on_update' => true,
            'upload_destination' => '/tmp',
            'inject_on_load' => true
        ));

        $this->assertEquals($prop->getUploadDir(), '/tmp');
        $this->assertTrue($prop->getDeleteOnRemove());
        $this->assertTrue($prop->getInjectOnLoad());
    }
}
