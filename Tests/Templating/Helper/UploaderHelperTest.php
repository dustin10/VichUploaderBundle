<?php


namespace Vich\UploaderBundle\Tests\Templating\Helper;


use PHPUnit_Framework_MockObject_MockObject;
use Vich\UploaderBundle\Exception\MissingMappingException;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class UploaderHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $storage;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $mappingFactory;

    protected function setUp()
    {
        $this->storage = $this->getMockBuilder('Vich\UploaderBundle\Storage\StorageInterface')
            ->getMock();

        $this->mappingFactory = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMappingFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new UploaderHelper($this->storage, $this->mappingFactory);
    }


    public function testAssetShouldResolvePathUsingMappingName()
    {
        $obj = new \stdClass();
        $fileIdentifier = 'foo_mapping_name';
        $className = null;

        $this->storage->expects($this->atLeastOnce())
            ->method('resolveUri')
            ->with($obj, $fileIdentifier, $className)
            ->willReturn('foo/asset/path');

        $this->assertEquals('foo/asset/path', $this->helper->asset($obj, $fileIdentifier, $className));
    }

    public function testAssetShouldFallbackToFieldIfMappingNameDoesNotExists()
    {
        $obj = new \stdClass();
        $fileIdentifier = 'objectFieldName';
        $className = null;

        $propertyMapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();

        $propertyMapping->expects($this->any())
            ->method('getMappingName')
            ->willReturn('foo_mapping_name');

        $this->storage->expects($this->at(0))
            ->method('resolveUri')
            ->with($obj, $fileIdentifier, $className)
            ->willThrowException(new MissingMappingException('Mapping foo_mapping_name does not exist'));

        $this->mappingFactory->expects($this->any())
            ->method('fromField')
            ->with($obj, $fileIdentifier, $className)
            ->willReturn($propertyMapping);

        $this->storage->expects($this->at(1))
            ->method('resolveUri')
            ->with($obj, 'foo_mapping_name', $className)
            ->willReturn('foo/asset/path');

        $this->assertEquals('foo/asset/path', $this->helper->asset($obj, $fileIdentifier, $className));
    }
}