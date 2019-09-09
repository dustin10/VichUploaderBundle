<?php

namespace Vich\UploaderBundle\Tests\EventListener\Propel;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * Propel listener test case.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class ListenerTestCase extends TestCase
{
    const FIELD_NAME = 'file';

    const MAPPING_NAME = 'mapping_name';

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var UploadHandler
     */
    protected $handler;

    /**
     * @var MetadataReader
     */
    protected $metadata;

    /**
     * @var PropelUploaderListener
     */
    protected $listener;

    /**
     * @var EventArgs
     */
    protected $event;

    /**
     * @var DummyEntity
     */
    protected $object;

    /**
     * Sets up the test.
     */
    protected function setUp(): void
    {
        $this->adapter = $this->getAdapterMock();
        $this->handler = $this->getHandlerMock();
        $this->object = new DummyEntity();
        $this->event = $this->getEventMock();
        $this->metadata = $this->getMetadataReaderMock();

        // the adapter is always used to return the object
        $this->adapter
            ->expects($this->any())
            ->method('getObjectFromArgs')
            ->with($this->event)
            ->willReturn($this->object);

        $this->metadata
            ->expects($this->any())
            ->method('getUploadableFields')
            ->with(DummyEntity::class, self::MAPPING_NAME)
            ->willReturn([
                ['propertyName' => self::FIELD_NAME],
            ]);
    }

    /**
     * Creates a mock adapter.
     *
     * @return AdapterInterface The mock adapter
     */
    protected function getAdapterMock()
    {
        return $this->createMock(AdapterInterface::class);
    }

    /**
     * Creates a mock handler.
     *
     * @return UploadHandler The handler mock
     */
    protected function getHandlerMock()
    {
        return $this->getMockBuilder(UploadHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock event.
     *
     * @return GenericEvent The mock event
     */
    protected function getEventMock()
    {
        return $this->getMockBuilder(GenericEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock metadata reader.
     *
     * @return MetadataReader The mock metadata reader
     */
    protected function getMetadataReaderMock()
    {
        return $this->getMockBuilder(MetadataReader::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
