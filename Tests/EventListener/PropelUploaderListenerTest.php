<?php

namespace Vich\UploaderBundle\Tests\EventListener;

use Vich\UploaderBundle\EventListener\PropelUploaderListener;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * PropelUploaderListenerTest
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class PropelUploaderListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vich\UploaderBundle\Adapter\AdapterInterface $adapter
     */
    protected $adapter;

    /**
     * @var \Vich\UploaderBundle\Handler\UploadHandler $handler
     */
    protected $handler;

    /**
     * @var \Vich\UploaderBundle\EventListener\PropelUploaderListener $listener
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

        $this->listener = new PropelUploaderListener($this->adapter, $this->handler);
    }

    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents()
    {
        $events = $this->listener->getSubscribedEvents();

        $this->assertArrayHasKey('propel.pre_insert', $events);
        $this->assertArrayHasKey('propel.pre_update', $events);
        $this->assertArrayHasKey('propel.post_delete', $events);
        $this->assertArrayHasKey('propel.post_hydrate', $events);
    }

    public function testOnUpload()
    {
        $this->handler
            ->expects($this->once())
            ->method('handleUpload')
            ->with($this->object);

        $this->listener->onUpload($this->event);
    }

    public function testOnHydrate()
    {
        $this->handler
            ->expects($this->once())
            ->method('handleHydration')
            ->with($this->object);

        $this->listener->onHydrate($this->event);
    }

    public function testOnDelete()
    {
        $this->handler
            ->expects($this->once())
            ->method('handleDeletion')
            ->with($this->object);

        $this->listener->onDelete($this->event);
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
     * @return \Vich\UploaderBundle\Handler\UploadHandler The mock handler.
     */
    protected function getHandlerMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Handler\UploadHandler')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock event
     *
     * @return Symfony\Component\EventDispatcher\GenericEvent
     */
    protected function getEventMock()
    {
        return $this->getMockBuilder('Symfony\Component\EventDispatcher\GenericEvent')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
