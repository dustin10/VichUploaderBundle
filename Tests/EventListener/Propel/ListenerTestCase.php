<?php

namespace Vich\UploaderBundle\Tests\EventListener\Propel;

use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * Propel listener test case.
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
     * @var \Vich\UploaderBundle\Handler\UploadHandler $handler
     */
    protected $handler;

    /**
     * @var PropelUploaderListener $listener
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
     * Sets up the test
     */
    public function setUp()
    {
        $this->adapter = $this->getAdapterMock();
        $this->handler = $this->getHandlerMock();
        $this->object = new DummyEntity();
        $this->event = $this->getEventMock();

        // the adapter is always used to return the object
        $this->adapter
            ->expects($this->any())
            ->method('getObjectFromArgs')
            ->with($this->event)
            ->will($this->returnValue($this->object));

        // in these tests, the adapter is always used with the same object
        $this->adapter
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue(get_class($this->object)));
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
     * Creates a mock event.
     *
     * @return \Symfony\Component\EventDispatcher\GenericEvent The mock event.
     */
    protected function getEventMock()
    {
        return $this->getMockBuilder('\Symfony\Component\EventDispatcher\GenericEvent')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
