<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\EventListener\Doctrine\BaseListener;
use Vich\UploaderBundle\Handler\UploadHandlerInterface;
use Vich\UploaderBundle\Metadata\MetadataReaderInterface;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 *
 * @template T of BaseListener
 */
abstract class ListenerTestCase extends TestCase
{
    public const string MAPPING_NAME = 'dummy_mapping';

    public static bool $usePreUpdateEventArgs = false;

    protected AdapterInterface&MockObject $adapter;

    protected MetadataReaderInterface&MockObject $metadata;

    protected UploadHandlerInterface&MockObject $handler;

    protected LifecycleEventArgs&Stub $event;

    public DummyEntity|MockObject $object;

    /** @var T */
    protected BaseListener $listener;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->metadata = $this->getMetadataReaderMock();
        $this->handler = $this->getHandlerMock();
        $this->object = new DummyEntity();
        $this->event = $this->getEventStub();
        $this->event->method('getObject')->willReturn($this->object);
    }

    protected function getHandlerMock(): UploadHandlerInterface&MockObject
    {
        return $this->createMock(UploadHandlerInterface::class);
    }

    protected function getEventStub(): LifecycleEventArgs&Stub
    {
        return $this->createStub(LifecycleEventArgs::class);
    }
}
