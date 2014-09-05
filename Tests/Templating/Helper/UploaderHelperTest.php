<?php


namespace Vich\UploaderBundle\Tests\Templating\Helper;


use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class UploaderHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testAssetShouldResolvePathUsingMappingName()
    {
        $obj = new \stdClass();
        $mappingName = 'foo_mapping_name';
        $className = null;

        $storage = $this->getMockBuilder('Vich\UploaderBundle\Storage\StorageInterface')
            ->getMock();

        $storage->expects($this->atLeastOnce())
            ->method('resolveUri')
            ->with($obj, $mappingName, $className)
            ->willReturn('foo/asset/path');

        $helper = new UploaderHelper($storage);

        $this->assertEquals('foo/asset/path', $helper->asset($obj, $mappingName, $className));
    }
}