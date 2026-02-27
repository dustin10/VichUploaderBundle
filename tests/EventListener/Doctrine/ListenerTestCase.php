<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\EventListener\Doctrine\BaseListener;
use Vich\UploaderBundle\Handler\UploadHandlerInterface;
use Vich\UploaderBundle\Metadata\MetadataReaderInterface;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * Doctrine listener test case.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 *
 * @template T of BaseListener
 */
abstract class ListenerTestCase extends TestCase
{
    public const MAPPING_NAME = 'dummy_mapping';

    public static bool $usePreUpdateEventArgs = false;

    protected AdapterInterface|MockObject $adapter;

    protected MetadataReaderInterface|MockObject $metadata;

    protected UploadHandlerInterface|MockObject $handler;

    protected LifecycleEventArgs|MockObject $event;

    public DummyEntity|MockObject $object;

    /** @var T */
    protected BaseListener $listener;

    /**
     * Sets up the test.
     */
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

    /**
     * @return MetadataReaderInterface&MockObject
     */
    protected function getMetadataReaderMock(): MetadataReaderInterface
    {
        return $this->createMock(MetadataReaderInterface::class);
    }

    /**
     * @return UploadHandlerInterface&MockObject
     */
    protected function getHandlerMock(): UploadHandlerInterface
    {
        return $this->createMock(UploadHandlerInterface::class);
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
