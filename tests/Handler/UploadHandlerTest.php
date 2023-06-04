<?php

namespace Vich\UploaderBundle\Tests\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Vich\TestBundle\Entity\Article;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
final class UploadHandlerTest extends TestCase
{
    protected MockObject|\Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory;

    protected StorageInterface|MockObject $storage;

    protected FileInjectorInterface|MockObject $injector;

    protected MockObject|EventDispatcherInterface $dispatcher;

    protected MockObject|PropertyMapping $mapping;

    protected Article $object;

    protected UploadHandler $handler;

    private const FILE_FIELD = 'image';

    protected function setUp(): void
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->storage = $this->getStorageMock();
        $this->injector = $this->getInjectorMock();
        $this->dispatcher = $this->getDispatcherMock();
        $this->mapping = $this->getPropertyMappingMock();
        $this->object = new Article();

        $this->handler = new UploadHandler($this->factory, $this->storage, $this->injector, $this->dispatcher);
        $this->factory
            ->method('fromField')
            ->with($this->object, self::FILE_FIELD)
            ->willReturn($this->mapping);
    }

    public function testUpload(): void
    {
        $this->expectEvents([Events::PRE_UPLOAD, Events::POST_UPLOAD]);

        $this->mapping
            ->expects(self::once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn($this->getUploadedFileMock());

        $this->storage
            ->expects(self::once())
            ->method('upload')
            ->with($this->object, $this->mapping);

        $this->injector
            ->expects(self::once())
            ->method('injectFile')
            ->with($this->object, $this->mapping);

        $this->handler->upload($this->object, self::FILE_FIELD);
    }

    /**
     * @dataProvider methodProvider
     */
    public function testAnExceptionIsThrownIfMappingIsntFound(string $method): void
    {
        $this->expectException(MappingNotFoundException::class);

        $this->factory = $this->getPropertyMappingFactoryMock();
        $handler = new UploadHandler($this->factory, $this->storage, $this->injector, $this->dispatcher);

        $handler->$method($this->object, self::FILE_FIELD);
    }

    public static function methodProvider(): array
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
            ->expects(self::once())
            ->method('injectFile')
            ->with($this->object, $this->mapping);

        $this->handler->inject($this->object, self::FILE_FIELD);
    }

    public function testClean(): void
    {
        $this->expectEvents([Events::PRE_REMOVE, Events::POST_REMOVE]);

        $this->mapping
            ->expects(self::once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn($this->getUploadedFileMock());

        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->with($this->object)
            ->willReturn('something not null');

        $this->storage
            ->expects(self::once())
            ->method('remove')
            ->with($this->object, $this->mapping);

        $this->handler->clean($this->object, self::FILE_FIELD);
    }

    public function testCleanSkipsEmptyObjects(): void
    {
        $this->mapping
            ->method('getFileName')
            ->with($this->object)
            ->willReturn('something not null');

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
            ->expects(self::once())
            ->method('getFileName')
            ->with($this->object)
            ->willReturn('something not null');

        $this->mapping
            ->expects(self::once())
            ->method('erase')
            ->with($this->object);

        $this->storage
            ->expects(self::once())
            ->method('remove')
            ->with($this->object, $this->mapping);

        $this->handler->remove($this->object, self::FILE_FIELD);
    }

    public function testRemoveIfEventIsCanceled(): void
    {
        $this->expectEvents([Events::PRE_REMOVE]);

        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->with($this->object)
            ->willReturn('something not null');

        $this->mapping
            ->expects($this->never())
            ->method('erase');

        $this->storage
            ->expects($this->never())
            ->method('remove');

        $eventListener = static function ($event): void {
            $event->cancel();
        };

        $this->dispatcher
            ->method('dispatch')
            ->willReturnCallback($eventListener);

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

    /**
     * @return StorageInterface&MockObject
     */
    protected function getStorageMock(): StorageInterface
    {
        return $this->createMock(StorageInterface::class);
    }

    /**
     * @return FileInjectorInterface&MockObject
     */
    protected function getInjectorMock(): FileInjectorInterface
    {
        return $this->createMock(FileInjectorInterface::class);
    }

    /**
     * @return EventDispatcherInterface&MockObject
     */
    protected function getDispatcherMock(): EventDispatcherInterface
    {
        return $this->createMock(EventDispatcherInterface::class);
    }

    protected function validEvent(): object
    {
        $object = $this->object;
        $mapping = $this->mapping;

        return self::callback(static fn ($event) => $event instanceof Event && $event->getObject() === $object && $event->getMapping() === $mapping);
    }

    protected function expectEvents(array $events): void
    {
        $arguments = \array_map(fn (string $event): array => [$this->validEvent(), $event], $events);

        $this->dispatcher
            ->expects(self::exactly(\count($events)))
            ->method('dispatch')
            ->withConsecutive(...$arguments)
        ;
    }
}
