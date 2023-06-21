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
 * Doctrine listener test case.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 *
 * @template-covariant T of BaseListener
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
     * @return AdapterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getAdapterMock(): AdapterInterface
    {
        return $this->createMock(AdapterInterface::class);
    }

    /**
     * @return MetadataReader&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMetadataReaderMock(): MetadataReader
    {
        return $this->getMockBuilder(MetadataReader::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return UploadHandler&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getHandlerMock(): UploadHandler
    {
        return $this->getMockBuilder(UploadHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return LifecycleEventArgs&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getEventMock(): LifecycleEventArgs
    {
        return $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
