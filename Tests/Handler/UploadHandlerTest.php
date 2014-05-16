<?php

namespace Vich\UploaderBundle\Tests\EventListener;

use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $storage;
    protected $injector;
    protected $dispatcher;

    protected $handler;

    public function setUp()
    {
        $this->storage = $this->getStorageMock();
        $this->injector = $this->getInjectorMock();
        $this->dispatcher = $this->getDispatcherMock();
        $this->object = new DummyEntity();

        $this->handler = new UploadHandler($this->storage, $this->injector, $this->dispatcher);
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
            ->with($this->object);

        $this->injector
            ->expects($this->once())
            ->method('injectFiles')
            ->with($this->object);

        $this->handler->handleUpload($this->object);
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
            ->method('injectFiles')
            ->with($this->object);

        $this->handler->handleHydration($this->object);
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
            ->with($this->object);

        $this->handler->handleDeletion($this->object);
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

    protected function validEvent()
    {
        $object = $this->object;

        return $this->callback(function($event) use ($object) {
            return $event instanceof Event && $event->getObject() === $object;
        });
    }
}
