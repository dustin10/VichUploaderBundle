<?php

namespace Vich\UploaderBundle\Tests\Handler;

use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadHandlerTest extends TestCase
{
    protected $factory;
    protected $storage;
    protected $injector;
    protected $dispatcher;
    protected $mapping;
    protected $object;
    protected $handler;

    const FILE_FIELD = 'file_field';

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
            ->expects($this->any())
            ->method('fromField')
            ->with($this->object, self::FILE_FIELD)
            ->will($this->returnValue($this->mapping));
    }

    public function testUpload()
    {
        $this->expectEvents(array(Events::PRE_UPLOAD, Events::POST_UPLOAD));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($this->getUploadedFileMock()));

        $this->storage
            ->expects($this->once())
            ->method('upload')
            ->with($this->object, $this->mapping);

        $this->injector
            ->expects($this->once())
            ->method('injectFile')
            ->with($this->object, $this->mapping);

        $this->handler->upload($this->object, self::FILE_FIELD);
    }

    /**
     * @dataProvider methodProvider
     * @expectedException \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function testAnExceptionIsThrownIfMappingIsntFound($method)
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $handler = new UploadHandler($this->factory, $this->storage, $this->injector, $this->dispatcher);

        call_user_func(array($handler, $method), $this->object, self::FILE_FIELD);
    }

    public function methodProvider()
    {
        return array(
            array('upload'),
            array('inject'),
            array('remove'),
            array('clean'),
        );
    }

    public function testUploadSkipsEmptyObjects()
    {
        $this->dispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->storage
            ->expects($this->never())
            ->method('upload');

        $this->injector
            ->expects($this->never())
            ->method('injectFile');

        $this->handler->upload($this->object, self::FILE_FIELD);
    }

    public function testInject()
    {
        $this->expectEvents(array(Events::PRE_INJECT, Events::POST_INJECT));

        $this->injector
            ->expects($this->once())
            ->method('injectFile')
            ->with($this->object, $this->mapping);

        $this->handler->inject($this->object, self::FILE_FIELD);
    }

    public function testClean()
    {
        $this->expectEvents(array(Events::PRE_REMOVE, Events::POST_REMOVE));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($this->getUploadedFileMock()));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->will($this->returnValue('something not null'));

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, $this->mapping);

        $this->handler->clean($this->object, self::FILE_FIELD);
    }

    public function testCleanSkipsEmptyObjects()
    {
        $this->mapping
            ->expects($this->any())
            ->method('getFileName')
            ->with($this->object)
            ->will($this->returnValue('something not null'));

        $this->dispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->storage
            ->expects($this->never())
            ->method('remove');

        $this->handler->clean($this->object, self::FILE_FIELD);
    }

    public function testRemove()
    {
        $this->expectEvents(array(Events::PRE_REMOVE, Events::POST_REMOVE));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->will($this->returnValue('something not null'));

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, $this->mapping);

        $this->handler->remove($this->object, self::FILE_FIELD);
    }

    public function testRemoveWithEmptyObject()
    {
        $this->dispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->storage
            ->expects($this->never())
            ->method('remove')
            ->with($this->object, $this->mapping);

        $this->handler->remove($this->object, self::FILE_FIELD);
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
        $mapping = $this->mapping;

        return $this->callback(function ($event) use ($object, $mapping) {
            return $event instanceof Event && $event->getObject() === $object && $event->getMapping() === $mapping;
        });
    }

    protected function expectEvents(array $events)
    {
        foreach ($events as $i => $event) {
            $this->dispatcher
                ->expects($this->at($i))
                ->method('dispatch')
                ->with($event, $this->validEvent());
        }
    }
}
