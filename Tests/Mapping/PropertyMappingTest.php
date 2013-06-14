<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\DummyDirectoryNamer;
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

    public function testResolveUriWithDirectoryNamer()
    {
        $mapping['uri_prefix'] = '/uploads';
        $uriPrefix             = '/uploads/custom/dir';
        $directoryNamerResult  = '/custom/dir';

        $obj = new DummyEntity();
        $directoryNamer = new DummyDirectoryNamer($directoryNamerResult);
        $propertyMapping = new PropertyMapping();

        $propertyMapping->setMapping($mapping);
        $propertyMapping->setDirectoryNamer($directoryNamer);

        $this->assertEquals($uriPrefix, $propertyMapping->getUriPrefix($obj, 'file'));
    }
}
