<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\EventListener\Doctrine\BaseListener;
use Vich\UploaderBundle\Handler\UploadHandler;
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

    protected AdapterInterface|MockObject $adapter;

    protected MetadataReaderInterface|MockObject $metadata;

    protected UploadHandler|MockObject $handler;

    protected LifecycleEventArgs|MockObject $event;

    public DummyEntity|MockObject $object;

    /** @var T */
    protected BaseListener $listener;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->metadata = $this->getMetadataReaderMock();
        $this->handler = $this->getUploadHandlerMock();
        $this->object = new DummyEntity();
        $this->event = $this->getEventMock();
        $this->event->method('getObject')->willReturn($this->object);
    }

    /**
     * @return AdapterInterface&MockObject
     */
    protected function getAdapterMock(): AdapterInterface
    {
        return $this->createMock(AdapterInterface::class);
    }

    protected function getMetadataReaderMock(): MetadataReaderInterface&MockObject
    {
        return $this->getMockBuilder(MetadataReaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getHandlerMock(): UploadHandlerInterface&MockObject
    {
        return $this->getMockBuilder(UploadHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return LifecycleEventArgs&MockObject
     */
    protected function getEventMock(): LifecycleEventArgs
    {
        return $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
