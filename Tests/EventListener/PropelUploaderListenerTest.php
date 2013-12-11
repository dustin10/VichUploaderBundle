<?php

namespace Vich\UploaderBundle\Tests\EventListener;

use Vich\UploaderBundle\EventListener\PropelUploaderListener;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * PropelUploaderListenerTest.
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
     * @var PropelUploaderListener $listener
     */
    protected $listener;

    /**
     * Sets up the test
     */
    public function setUp()
    {
        $this->adapter = $this->getAdapterMock();
        $this->handler = $this->getHandlerMock();
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
        $this->assertArrayHasKey('propel.construct', $events);
    }

    public function testOnUpload()
    {
        $obj = new DummyEntity();
        $event = $this->getEventMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromEvent')
            ->will($this->returnValue($obj));

        $this->handler
            ->expects($this->once())
            ->method('handleUpload')
            ->with($obj);

        $this->listener->onUpload($event);
    }

    public function testOnConstruct()
    {
        $obj = new DummyEntity();
        $event = $this->getEventMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromEvent')
            ->will($this->returnValue($obj));

        $this->handler
            ->expects($this->once())
            ->method('handleHydration')
            ->with($obj);

        $this->listener->onConstruct($event);
    }

    public function testOnDelete()
    {
        $obj = new DummyEntity();
        $event = $this->getEventMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromEvent')
            ->will($this->returnValue($obj));

        $this->handler
            ->expects($this->once())
            ->method('handleDeletion')
            ->with($obj);

        $this->listener->onDelete($event);
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
