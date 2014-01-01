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
        $prop = new PropertyMapping('file', 'fileName');
        $prop->setMapping(array(
            'upload_destination' => '/tmp',
        ));

        $this->assertEquals('/tmp', $prop->getUploadDestination());
    }
}
