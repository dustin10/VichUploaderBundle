<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\EventListener\Doctrine\BaseListener;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 *
 * @template T of BaseListener
 */
abstract class ListenerTestCase extends TestCase
{
    public const MAPPING_NAME = 'dummy_mapping';

    public static bool $usePreUpdateEventArgs = false;

    protected AdapterInterface|MockObject $adapter;

    protected MetadataReader|MockObject $metadata;

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

    protected function getMetadataReaderMock(): MetadataReader|MockObject
    {
        return $this->createMock(MetadataReader::class);
    }

    protected function getHandlerMock(): UploadHandler|MockObject
    {
        return $this->getMockBuilder(UploadHandler::class)
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
