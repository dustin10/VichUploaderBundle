<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * Doctrine listener test case.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class ListenerTestCase extends \PHPUnit_Framework_TestCase
{
    const MAPPING_NAME = 'dummy_mapping';

    /**
     * @var \Vich\UploaderBundle\Adapter\AdapterInterface $adapter
     */
    protected $adapter;

    /**
     * @var \Vich\UploaderBundle\Metadata\MetadataReader $metadata
     */
    protected $metadata;

    /**
     * @var \Vich\UploaderBundle\Handler\UploadHandler $handler
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

    /**
     * Sets up the test
     */
    public function setUp()
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

    /**
     * Creates a mock adapter.
     *
     * @return \Vich\UploaderBundle\Adapter\AdapterInterface The mock adapter.
     */
    protected function getAdapterMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock metadata reader.
     *
     * @return \Vich\UploaderBundle\Metadata\MetadataReader The mock metadata reader.
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
     * @return \Vich\UploaderBundle\Handler\UploadHandler The handler mock.
     */
    protected function getHandlerMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Handler\UploadHandler')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock doctrine event
     *
     * @return \Doctrine\Common\EventArgs
     */
    protected function getEventMock()
    {
        return $this->getMockBuilder('Doctrine\Common\EventArgs')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
