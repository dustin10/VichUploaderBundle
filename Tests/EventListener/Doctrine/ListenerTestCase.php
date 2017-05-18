<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Common\EventArgs;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Adapter\DoctrineAdapter;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class ListenerTestCase extends TestCase
{
    const MAPPING_NAME = 'dummy_mapping';

    /**
     * @var DoctrineAdapter
     */
    protected $adapter;

    /**
     * @var MetadataReader
     */
    protected $metadata;

    /**
     * @var UploadHandler
     */
    protected $handler;

    /**
     * @var EventArgs
     */
    protected $event;

    /**
     * @var DummyEntity
     */
    public $object;

    protected $listener;

    protected function setUp()
    {
        $this->adapter = $this->getAdapterMock();
        $this->metadata = $this->getMetadataReaderMock();
        $this->handler = $this->getHandlerMock();
        $this->object = new DummyEntity();
        $this->event = $this->getEventMock();

        $that = $this;

        // the adapter is always used to return the object
        $this->adapter
            ->expects($this->any())
            ->method('getObjectFromArgs')
            ->with($this->event)
            ->will($this->returnCallback(function () use ($that) {
                return $that->object;
            }));
    }

    protected function getAdapterMock()
    {
        return $this->createMock(AdapterInterface::class);
    }

    /**
     * Creates a mock metadata reader.
     *
     * @return MetadataReader The mock metadata reader
     */
    protected function getMetadataReaderMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Metadata\MetadataReader')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock handler.
     *
     * @return UploadHandler The handler mock
     */
    protected function getHandlerMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Handler\UploadHandler')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock doctrine event.
     *
     * @return EventArgs
     */
    protected function getEventMock()
    {
        return $this->getMockBuilder('Doctrine\Common\EventArgs')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
