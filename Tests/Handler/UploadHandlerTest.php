<?php

namespace Vich\UploaderBundle\Tests\Handler;

use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;
    protected $storage;
    protected $injector;
    protected $dispatcher;

    protected $handler;

    const MAPPING_ID = 'mapping_id';

    public function setUp()
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->storage = $this->getStorageMock();
        $this->injector = $this->getInjectorMock();
        $this->dispatcher = $this->getDispatcherMock();
        $this->mapping = $this->getPropertyMappingMock();
        $this->object = new DummyEntity();

        $this->handler = new UploadHandler($this->factory, $this->storage, $this->injector, $this->dispatcher);
        $this->factory
            ->expects($this->once())
            ->method('fromName')
            ->with($this->object, self::MAPPING_ID)
            ->will($this->returnValue($this->mapping));
    }

    public function testHandleUpload()
    {
        $this->dispatcher
            ->expects($this->any())
            ->method('dispatch')
            ->will($this->returnValueMap(array(
                array(Events::PRE_UPLOAD, $this->validEvent(), null),
                array(Events::POST_UPLOAD, $this->validEvent(), null),
            )));

        $this->storage
            ->expects($this->once())
            ->method('upload')
            ->with($this->object, $this->mapping);

        $this->injector
            ->expects($this->once())
            ->method('injectFile')
            ->with($this->object, $this->mapping);

        $this->handler->handleUpload($this->object, self::MAPPING_ID);
    }

    public function testHandleHydration()
    {
        $this->dispatcher
            ->expects($this->any())
            ->method('dispatch')
            ->will($this->returnValueMap(array(
                array(Events::PRE_INJECT, $this->validEvent(), null),
                array(Events::POST_INJECT, $this->validEvent(), null),
            )));

        $this->injector
            ->expects($this->once())
            ->method('injectFile')
            ->with($this->object, $this->mapping);

        $this->handler->handleHydration($this->object, self::MAPPING_ID);
    }

    public function testHandleDeletion()
    {
        $this->dispatcher
            ->expects($this->any())
            ->method('dispatch')
            ->will($this->returnValueMap(array(
                array(Events::PRE_REMOVE, $this->validEvent(), null),
                array(Events::POST_REMOVE, $this->validEvent(), null),
            )));

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, $this->mapping);

        $this->handler->handleDeletion($this->object, self::MAPPING_ID);
    }

    protected function getStorageMock()
    {
        return $this->getMock('Vich\UploaderBundle\Storage\StorageInterface');
    }

    protected function getInjectorMock()
    {
        return $this->getMock('Vich\UploaderBundle\Injector\FileInjectorInterface');
    }

    protected function getDispatcherMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
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

    protected function validEvent()
    {
        $object = $this->object;

        return $this->callback(function ($event) use ($object) {
            return $event instanceof Event && $event->getObject() === $object;
        });
    }
}
