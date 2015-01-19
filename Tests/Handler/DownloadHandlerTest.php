<?php

namespace Vich\UploaderBundle\Tests\Handler;

use Vich\UploaderBundle\Handler\DownloadHandler;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class DownloadHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;
    protected $storage;
    protected $object;

    protected $handler;

    public function setUp()
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->storage = $this->getMock('Vich\UploaderBundle\Storage\StorageInterface');
        $this->mapping = $this->getPropertyMappingMock();
        $this->object = new DummyEntity();

        $this->handler = new DownloadHandler($this->factory, $this->storage);
        $this->factory
            ->expects($this->any())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->will($this->returnValue($this->mapping));
    }

    public function testDownloadObject()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->will($this->returnValue('file_name'));

        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field');

        $response = $this->handler->downloadObject($this->object, 'file_field');

        $this->assertInstanceof('\Symfony\Component\HttpFoundation\StreamedResponse', $response);
    }

    /**
     * @expectedException Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function testAnExceptionIsThrownIfMappingIsntFound()
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->handler = new DownloadHandler($this->factory, $this->storage);

        $this->handler->downloadObject($this->object, 'file_field');
    }

    /**
     * Creates a mock property mapping factory
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMappingFactory
     */
    protected function getPropertyMappingFactoryMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMappingFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Gets a mock property mapping.
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMapping
     */
    protected function getPropertyMappingMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
