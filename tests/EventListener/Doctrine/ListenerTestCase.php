<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
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
     * @var EventArgs|PreUpdateEventArgs|\PHPUnit\Framework\MockObject\MockObject
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

        $that = $this;

        // the adapter is always used to return the object
        $this->adapter
            ->method('getObjectFromArgs')
            ->with($this->event)
            ->willReturnCallback(fn () => $that->object);
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
     * @return EventArgs&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getEventMock(): EventArgs
    {
        return $this->getMockBuilder(EventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
