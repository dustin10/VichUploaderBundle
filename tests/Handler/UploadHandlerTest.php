<?php

namespace Vich\UploaderBundle\Tests\Handler;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Vich\TestBundle\Entity\Article;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactoryInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
#[AllowMockObjectsWithoutExpectations]
final class UploadHandlerTest extends TestCase
{
    protected PropertyMappingFactoryInterface&Stub $factory;

    protected StorageInterface&MockObject $storage;

    protected FileInjectorInterface&MockObject $injector;

    protected EventDispatcherInterface&MockObject $dispatcher;

    protected PropertyMappingInterface&MockObject $mapping;

    protected Article $object;

    protected UploadHandler $handler;

    private const FILE_FIELD = 'image';

    protected function setUp(): void
    {
        $this->factory = $this->getPropertyMappingFactoryStub();
        $this->storage = $this->getStorageMock();
        $this->injector = $this->getInjectorMock();
        $this->dispatcher = $this->getDispatcherMock();
        $this->mapping = $this->getPropertyMappingMock();
        $this->object = new Article();

        $this->handler = new UploadHandler($this->factory, $this->storage, $this->injector, $this->dispatcher);
        $this->factory
            ->method('fromField')
            ->willReturnMap([[$this->object, self::FILE_FIELD, null, $this->mapping]]);
    }

    #[Test]
    public function upload(): void
    {
        $this->expectEvents([Events::PRE_UPLOAD, Events::POST_UPLOAD]);

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn($this->getUploadedFileMock());

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

    #[DataProvider('methodProvider')]
    #[Test]
    public function anExceptionIsThrownIfMappingIsntFound(string $method): void
    {
        $this->expectException(MappingNotFoundException::class);

        $this->factory = $this->getPropertyMappingFactoryStub();
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

    #[Test]
    public function uploadSkipsEmptyObjects(): void
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

    #[Test]
    public function inject(): void
    {
        $this->expectEvents([Events::PRE_INJECT, Events::POST_INJECT]);

        $this->injector
            ->expects($this->once())
            ->method('injectFile')
            ->with($this->object, $this->mapping);

        $this->handler->inject($this->object, self::FILE_FIELD);
    }

    #[Test]
    public function clean(): void
    {
        $this->expectEvents([Events::PRE_REMOVE, Events::POST_REMOVE]);

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn($this->getUploadedFileMock());

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->willReturn('something not null');

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, $this->mapping);

        $this->handler->clean($this->object, self::FILE_FIELD);
    }

    #[Test]
    public function cleanSkipsEmptyObjects(): void
    {
        $this->mapping
            ->expects($this->never())
            ->method('getFileName');

        $this->dispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->storage
            ->expects($this->never())
            ->method('remove');

        $this->handler->clean($this->object, self::FILE_FIELD);
    }

    #[Test]
    public function remove(): void
    {
        $this->expectEvents([Events::PRE_REMOVE, Events::POST_REMOVE]);

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->willReturn('something not null');

        $this->mapping
            ->expects($this->once())
            ->method('erase')
            ->with($this->object);

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, $this->mapping);

        $this->handler->remove($this->object, self::FILE_FIELD);
    }

    #[Test]
    public function removeFailsInStorageDriverEmitsEvent(): void
    {
        $this->expectEvents([Events::PRE_REMOVE, Events::REMOVE_ERROR, Events::POST_REMOVE]);

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->willReturn('something not null');

        $this->mapping
            ->expects($this->once())
            ->method('erase')
            ->with($this->object);

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, $this->mapping)
            ->willThrowException(new \RuntimeException('Test exception'))
        ;

        $this->handler->remove($this->object, self::FILE_FIELD);
    }

    #[Test]
    public function uploadFailsEmitsEventAndException(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->expectEvents([Events::PRE_UPLOAD, Events::UPLOAD_ERROR]);

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn($this->getUploadedFileMock());

        $this->storage
            ->expects($this->once())
            ->method('upload')
            ->with($this->object, $this->mapping)
            ->willThrowException(new \RuntimeException('This is a test'));

        $this->injector
            ->expects(self::never())
            ->method('injectFile')
            ->with($this->object, $this->mapping);

        $this->handler->upload($this->object, self::FILE_FIELD);
    }

    public function testremoveFailsWithCannotWriteException(): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->willReturn('something not null');

        $this->mapping
            ->expects($this->once())
            ->method('erase')
            ->with($this->object);

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, $this->mapping)
            ->willThrowException(new CannotWriteFileException('this is a test'));

        $this->handler->remove($this->object, self::FILE_FIELD);
    }

    #[Test]
    public function removeIfEventIsCanceled(): void
    {
        $this->expectEvents([Events::PRE_REMOVE]);

        $this->mapping
            ->expects($this->once())
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

    #[Test]
    public function removeWithEmptyObject(): void
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

    protected function getStorageMock(): StorageInterface&MockObject
    {
        return $this->createMock(StorageInterface::class);
    }

    protected function getInjectorMock(): FileInjectorInterface&MockObject
    {
        return $this->createMock(FileInjectorInterface::class);
    }

    protected function getDispatcherMock(): EventDispatcherInterface&MockObject
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
        $this->dispatcher
            ->expects(self::exactly(\count($events)))
            ->method('dispatch')
            ->willReturnCallback(static fn (object $event, string $eventName): object => $event)
        ;
    }
}
