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
    protected function setUp()
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
            ->will($this->returnValue($this->object));

        $this->metadata
            ->expects($this->any())
            ->method('getUploadableFields')
            ->with('Vich\UploaderBundle\Tests\DummyEntity', self::MAPPING_NAME)
            ->will($this->returnValue([
                ['propertyName' => self::FIELD_NAME],
            ]));
    }

    /**
     * Creates a mock adapter.
     *
     * @return AdapterInterface The mock adapter
     */
    protected function getAdapterMock()
    {
        return $this->createMock('Vich\UploaderBundle\Adapter\AdapterInterface');
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
     * Creates a mock event.
     *
     * @return GenericEvent The mock event
     */
    protected function getEventMock()
    {
        return $this->getMockBuilder('\Symfony\Component\EventDispatcher\GenericEvent')
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
        return $this->getMockBuilder('Vich\UploaderBundle\Metadata\MetadataReader')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
