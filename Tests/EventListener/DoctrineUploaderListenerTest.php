<?php

namespace Vich\UploaderBundle\Tests\EventListener;

use Vich\UploaderBundle\EventListener\DoctrineUploaderListener;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * UploaderListenerTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class DoctrineUploaderListenerTest extends \PHPUnit_Framework_TestCase
{
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
     * @var \Vich\UploaderBundle\EventListener\DoctrineUploaderListener $listener
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
        $this->metadata = $this->getMetadataReaderMock();
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

        $this->listener = new DoctrineUploaderListener($this->adapter, $this->metadata, $this->handler);
    }

    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents()
    {
        $events = $this->listener->getSubscribedEvents();

        $this->assertContains('prePersist', $events);
        $this->assertContains('preUpdate', $events);
        $this->assertContains('postLoad', $events);
        $this->assertContains('postRemove', $events);
    }

    /**
     * Tests the prePersist method.
     */
    public function testPrePersist()
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->handler
            ->expects($this->once())
            ->method('handleUpload')
            ->with($this->object);

        $this->listener->prePersist($this->event);
    }

    /**
     * Tests that prePersist skips non-uploadable entity.
     */
    public function testPrePersistSkipsNonUploadable()
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(false));

        $this->handler
            ->expects($this->never())
            ->method('handleUpload');

        $this->listener->prePersist($this->event);
    }

    /**
     * Test the preUpdate method.
     */
    public function testPreUpdate()
    {
        $this->adapter
            ->expects($this->once())
            ->method('recomputeChangeSet')
            ->with($this->event);

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->handler
            ->expects($this->once())
            ->method('handleUpload')
            ->with($this->object);

        $this->listener->preUpdate($this->event);
    }

    /**
     * Test that preUpdate skips non uploadable entity.
     */
    public function testPreUpdateSkipsNonUploadable()
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(false));

        $this->handler
            ->expects($this->never())
            ->method('handleUpload');

        $this->adapter
            ->expects($this->never())
            ->method('recomputeChangeSet');

        $this->listener->preUpdate($this->event);
    }

    /**
     * Test the postLoad method.
     */
    public function testPostLoad()
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->handler
            ->expects($this->once())
            ->method('handleHydration')
            ->with($this->object);

        $this->listener->postLoad($this->event);
    }

    /**
     * Test that postLoad skips non uploadable entity.
     */
    public function testPostLoadSkipsNonUploadable()
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(false));

        $this->handler
            ->expects($this->never())
            ->method('handleHydration');

        $this->listener->postLoad($this->event);
    }

    /**
     * Test the postRemove method.
     */
    public function testPostRemove()
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->handler
            ->expects($this->once())
            ->method('handleDeletion')
            ->with($this->object);

        $this->listener->postRemove($this->event);
    }

    /**
     * Test that postRemove skips non uploadable entity.
     */
    public function testPostRemoveSkipsNonUploadable()
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(false));

        $this->handler
            ->expects($this->never())
            ->method('handleDeletion');

        $this->listener->postRemove($this->event);
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
     * @return \Vich\UploaderBundle\Handler\UploadHandler The mock handler.
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
