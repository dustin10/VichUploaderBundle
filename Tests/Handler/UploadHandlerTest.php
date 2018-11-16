<?php

namespace Vich\UploaderBundle\Tests\Handler;

use Vich\TestBundle\Entity\Article;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\Handler\RemoveHandler;
use Vich\UploaderBundle\Handler\UploadHandler;
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

    /**
     * @var Article
     */
    protected $object;

    protected $handler;

    protected $removeHandler;

    const FILE_FIELD = 'image';

    protected function setUp(): void
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->storage = $this->getStorageMock();
        $this->injector = $this->getInjectorMock();
        $this->dispatcher = $this->getDispatcherMock();
        $this->mapping = $this->getPropertyMappingMock();
        $this->removeHandler = $this->getRemoveHandlerMock();
        $this->object = new Article();

        $this->handler = new UploadHandler($this->factory, $this->storage, $this->injector, $this->dispatcher, $this->removeHandler);
        $this->factory
            ->expects($this->any())
            ->method('fromField')
            ->with($this->object, self::FILE_FIELD)
            ->will($this->returnValue($this->mapping));
    }

    public function testUpload(): void
    {
        $this->expectEvents([Events::PRE_UPLOAD, Events::POST_UPLOAD]);

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
     */
    public function testAnExceptionIsThrownIfMappingIsntFound($method): void
    {
        $this->expectException(\Vich\UploaderBundle\Exception\MappingNotFoundException::class);

        $this->factory = $this->getPropertyMappingFactoryMock();
        $removeHandler = $this->getRemoveHandlerMock();
        $handler = new UploadHandler($this->factory, $this->storage, $this->injector, $this->dispatcher, $removeHandler);

        \call_user_func([$handler, $method], $this->object, self::FILE_FIELD);
    }

    public function methodProvider(): array
    {
        return [
            ['upload'],
            ['inject'],
            ['remove'],
            ['clean'],
        ];
    }

    public function testUploadSkipsEmptyObjects(): void
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

    public function testInject(): void
    {
        $this->expectEvents([Events::PRE_INJECT, Events::POST_INJECT]);

        $this->injector
            ->expects($this->once())
            ->method('injectFile')
            ->with($this->object, $this->mapping);

        $this->handler->inject($this->object, self::FILE_FIELD);
    }

    public function testClean(): void
    {
        $this->expectEvents([Events::PRE_REMOVE, Events::POST_REMOVE]);

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

    public function testCleanSkipsEmptyObjects(): void
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

    public function testRemove(): void
    {
        $this->expectEvents([Events::PRE_REMOVE, Events::POST_REMOVE]);

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->will($this->returnValue('something not null'));

        $this->mapping
            ->expects($this->once())
            ->method('erase')
            ->with($this->object)
            ->will($this->returnValue(null));

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, $this->mapping);

        $this->handler->remove($this->object, self::FILE_FIELD);
    }

    public function testRemoveWithEmptyObject(): void
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
        return $this->createMock('Vich\UploaderBundle\Storage\StorageInterface');
    }

    protected function getInjectorMock()
    {
        return $this->createMock('Vich\UploaderBundle\Injector\FileInjectorInterface');
    }

    protected function getDispatcherMock()
    {
        return $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    protected function getRemoveHandlerMock()
    {
        return new RemoveHandler($this->factory, $this->storage, $this->dispatcher);
    }

    protected function validEvent()
    {
        $object = $this->object;
        $mapping = $this->mapping;

        return $this->callback(function ($event) use ($object, $mapping) {
            return $event instanceof Event && $event->getObject() === $object && $event->getMapping() === $mapping;
        });
    }

    protected function expectEvents(array $events): void
    {
        foreach ($events as $i => $event) {
            $this->dispatcher
                ->expects($this->at($i))
                ->method('dispatch')
                ->with($event, $this->validEvent());
        }
    }
}
