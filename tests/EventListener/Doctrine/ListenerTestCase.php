<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\EventListener\Doctrine;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * Doctrine listener test case.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
abstract class ListenerTestCase extends TestCase
{
    public const MAPPING_NAME = 'dummy_mapping';

    /**
     * @var bool
     */
    public static $usePreUpdateEventArgs = false;

    /**
     * @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $adapter;

    /**
     * @var MetadataReader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $metadata;

    /**
     * @var UploadHandler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $handler;

    /**
     * @var LifecycleEventArgs|PreUpdateEventArgs|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $event;

    /**
     * @var DummyEntity|\PHPUnit\Framework\MockObject\MockObject
     */
    public $object;

    /**
     * @var Doctrine\CleanListener|Doctrine\InjectListener|Doctrine\RemoveListener|Doctrine\UploadListener|null
     */
    protected $listener;

    /**
     * Sets up the test.
     */
    protected function setUp(): void
    {
        $this->adapter = $this->getAdapterMock();
        $this->metadata = $this->getMetadataReaderMock();
        $this->handler = $this->getHandlerMock();
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
