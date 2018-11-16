<?php

namespace Vich\UploaderBundle\Tests\Handler;

use Vich\TestBundle\Entity\Article;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\Handler\RemoveHandler;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Kim Wuestkamp <kim@wuestkamp.com>
 */
class RemoveHandlerTest extends TestCase
{
    protected $factory;

    protected $storage;

    protected $dispatcher;

    /**
     * @var RemoveHandler
     */
    protected $handler;

    const FILE_FIELD_ONE = 'image';

    const FILE_FIELD_TWO = 'attachment';

    protected function setUp(): void
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->storage = $this->getStorageMock();
        $this->dispatcher = $this->getDispatcherMock();

        $this->handler = new RemoveHandler($this->factory, $this->storage, $this->dispatcher);
    }

    public function testAddToRemoveQueue(): void
    {
        $object = new Article();

        $mapping = $this->getPropertyMappingMock();
        $this->factory->method('fromField')->will($this->returnValue($mapping));

        $this->dispatcherExceptsDispatch(0, Events::PRE_ADD_REMOVE_QUEUE, $object, $mapping);
        $this->dispatcherExceptsDispatch(1, Events::POST_ADD_REMOVE_QUEUE, $object, $mapping);

        $this->handler->addToQueue($object, self::FILE_FIELD_ONE);
    }

    public function testAddMultipleFieldsToRemoveQueue(): void
    {
        $object = new Article();

        $mapping = $this->getPropertyMappingMock();
        $this->factory->method('fromField')->will($this->returnValue($mapping));

        $this->dispatcherExceptsDispatch(0, Events::PRE_ADD_REMOVE_QUEUE, $object, $mapping);
        $this->dispatcherExceptsDispatch(1, Events::POST_ADD_REMOVE_QUEUE, $object, $mapping);
        $this->dispatcherExceptsDispatch(2, Events::PRE_ADD_REMOVE_QUEUE, $object, $mapping);
        $this->dispatcherExceptsDispatch(3, Events::POST_ADD_REMOVE_QUEUE, $object, $mapping);

        $queue = $this->handler->getQueue();
        $this->assertEquals(count($queue), 0);

        $this->handler->addToQueue($object, self::FILE_FIELD_ONE);
        $this->assertEquals(count($queue), 1);
        $this->assertEquals(count($queue[$object]['fieldNames']), 1);
        $this->assertEquals(
            $queue[$object]['fieldNames'],
            [
                self::FILE_FIELD_ONE,
            ]
        );

        $this->handler->addToQueue($object, self::FILE_FIELD_TWO);
        $this->assertEquals(count($queue), 1);
        $this->assertEquals(count($queue[$object]['fieldNames']), 2);
        $this->assertEquals(
            $queue[$object]['fieldNames'],
            [
                self::FILE_FIELD_TWO,
                self::FILE_FIELD_ONE,
            ]
        );
    }

    public function testAddMultipleObjectsWithMultipleFieldsToRemoveQueue(): void
    {
        $object1 = new Article();
        $object2 = new Article();

        $mapping1 = $this->getPropertyMappingMock();
        $mapping2 = $this->getPropertyMappingMock();

        $this->factoryExpectsFromField(0, $object1, self::FILE_FIELD_ONE, $mapping1);
        $this->factoryExpectsFromField(1, $object1, self::FILE_FIELD_TWO, $mapping1);
        $this->factoryExpectsFromField(2, $object2, self::FILE_FIELD_ONE, $mapping2);
        $this->factoryExpectsFromField(3, $object2, self::FILE_FIELD_TWO, $mapping2);

        $this->dispatcherExceptsDispatch(0, Events::PRE_ADD_REMOVE_QUEUE, $object1, $mapping1);
        $this->dispatcherExceptsDispatch(1, Events::POST_ADD_REMOVE_QUEUE, $object1, $mapping1);
        $this->dispatcherExceptsDispatch(2, Events::PRE_ADD_REMOVE_QUEUE, $object1, $mapping1);
        $this->dispatcherExceptsDispatch(3, Events::POST_ADD_REMOVE_QUEUE, $object1, $mapping1);
        $this->dispatcherExceptsDispatch(4, Events::PRE_ADD_REMOVE_QUEUE, $object2, $mapping2);
        $this->dispatcherExceptsDispatch(5, Events::POST_ADD_REMOVE_QUEUE, $object2, $mapping2);
        $this->dispatcherExceptsDispatch(6, Events::PRE_ADD_REMOVE_QUEUE, $object2, $mapping2);
        $this->dispatcherExceptsDispatch(7, Events::POST_ADD_REMOVE_QUEUE, $object2, $mapping2);

        $queue = $this->handler->getQueue();
        $this->assertEquals(count($queue), 0);

        $this->handler->addToQueue($object1, self::FILE_FIELD_ONE);
        $this->assertEquals(count($queue), 1);
        $this->assertEquals(
            $queue[$object1]['fieldNames'],
            [
                self::FILE_FIELD_ONE,
            ]
        );

        $this->handler->addToQueue($object1, self::FILE_FIELD_TWO);
        $this->assertEquals(count($queue), 1);
        $this->assertEquals(
            $queue[$object1]['fieldNames'],
            [
                self::FILE_FIELD_TWO,
                self::FILE_FIELD_ONE,
            ]
        );

        $this->handler->addToQueue($object2, self::FILE_FIELD_ONE);
        $this->assertEquals(count($queue), 2);
        $this->assertEquals(
            $queue[$object2]['fieldNames'],
            [
                self::FILE_FIELD_ONE,
            ]
        );

        $this->handler->addToQueue($object2, self::FILE_FIELD_TWO);
        $this->assertEquals(count($queue), 2);
        $this->assertEquals(
            $queue[$object2]['fieldNames'],
            [
                self::FILE_FIELD_TWO,
                self::FILE_FIELD_ONE,
            ]
        );
    }

    public function testRemoveFilesInQueue(): void
    {
        $object1 = new Article();
        $object2 = new Article();

        $mapping11 = $this->getPropertyMappingMock();
        $mapping12 = $this->getPropertyMappingMock();
        $mapping21 = $this->getPropertyMappingMock();
        $mapping22 = $this->getPropertyMappingMock();

        // factory calls during addToQueue
        $this->factoryExpectsFromField(0, $object1, self::FILE_FIELD_ONE, $mapping11);
        $this->factoryExpectsFromField(1, $object1, self::FILE_FIELD_TWO, $mapping12);
        $this->factoryExpectsFromField(2, $object2, self::FILE_FIELD_ONE, $mapping21);
        $this->factoryExpectsFromField(3, $object2, self::FILE_FIELD_TWO, $mapping21);

        // factory calls during remove
        $this->factoryExpectsFromField(4, $object1, self::FILE_FIELD_TWO, $mapping12);
        $this->factoryExpectsFromField(5, $object1, self::FILE_FIELD_ONE, $mapping11);
        $this->factoryExpectsFromField(6, $object2, self::FILE_FIELD_TWO, $mapping22);
        $this->factoryExpectsFromField(7, $object2, self::FILE_FIELD_ONE, $mapping21);

        $this->handler->addToQueue($object1, self::FILE_FIELD_ONE);
        $this->handler->addToQueue($object1, self::FILE_FIELD_TWO);
        $this->handler->addToQueue($object2, self::FILE_FIELD_ONE);
        $this->handler->addToQueue($object2, self::FILE_FIELD_TWO);

        $objects = $this->handler->removeFilesInQueue();

        $this->assertEquals(count($objects), 2);
        $this->assertTrue($objects[0] === $object1);
        $this->assertTrue($objects[1] === $object2);
    }

    protected function getStorageMock()
    {
        return $this->createMock('Vich\UploaderBundle\Storage\StorageInterface');
    }

    protected function getDispatcherMock()
    {
        return $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    protected function validEvent($object, $mapping)
    {
        return $this->callback(
            function ($event) use ($object, $mapping) {
                return $event instanceof Event && $event->getObject() === $object && $event->getMapping() === $mapping;
            }
        );
    }

    protected function dispatcherExceptsDispatch($at, $event, $object, $mapping)
    {
        $this->dispatcher
            ->expects($this->at($at))
            ->method('dispatch')
            ->with($event, $this->validEvent($object, $mapping));
    }

    protected function factoryExpectsFromField($at, $object, $fieldName, $mapping)
    {
        $this->factory
            ->expects($this->at($at))
            ->method('fromField')
            ->with($object, $fieldName)
            ->will($this->returnValue($mapping));
    }
}
