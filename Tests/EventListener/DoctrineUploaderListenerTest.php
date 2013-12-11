<?php

namespace Vich\UploaderBundle\Tests\EventListener;

use Vich\UploaderBundle\EventListener\DoctrineUploaderListener;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * DoctrineUploaderListenerTest.
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
     * Sets up the test
     */
    public function setUp()
    {
        $this->adapter = $this->getAdapterMock();
        $this->metadata = $this->getMetadataReaderMock();
        $this->handler = $this->getHandlerMock();
    }

    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents()
    {
        $listener = new DoctrineUploaderListener($this->adapter, $this->metadata, $this->handler);
        $events = $listener->getSubscribedEvents();

        $this->assertTrue(in_array('prePersist', $events));
        $this->assertTrue(in_array('preUpdate', $events));
        $this->assertTrue(in_array('postLoad', $events));
        $this->assertTrue(in_array('postRemove', $events));
    }

    /**
     * Tests the prePersist method.
     */
    public function testPrePersist()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
                ->disableOriginalConstructor()
                ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromEvent')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($class)
            ->will($this->returnValue(true));

        $this->handler
            ->expects($this->once())
            ->method('handleUpload')
            ->with($obj);

        $listener = new DoctrineUploaderListener($this->adapter, $this->metadata, $this->handler);
        $listener->prePersist($args);
    }

    /**
     * Tests that prePersist skips non-uploadable entity.
     */
    public function testPrePersistSkipsNonUploadable()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
                ->disableOriginalConstructor()
                ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromEvent')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($class)
            ->will($this->returnValue(false));

        $this->handler
            ->expects($this->never())
            ->method('handleUpload');

        $listener = new DoctrineUploaderListener($this->adapter, $this->metadata, $this->handler);
        $listener->prePersist($args);
    }

    /**
     * Test the preUpdate method.
     */
    public function testPreUpdate()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
                ->disableOriginalConstructor()
                ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromEvent')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->adapter
            ->expects($this->once())
            ->method('recomputeChangeSet')
            ->with($args);

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($class)
            ->will($this->returnValue(true));

        $this->handler
            ->expects($this->once())
            ->method('handleUpload')
            ->with($obj);

        $listener = new DoctrineUploaderListener($this->adapter, $this->metadata, $this->handler);
        $listener->preUpdate($args);
    }

    /**
     * Test that preUpdate skips non uploadable entity.
     */
    public function testPreUpdateSkipsNonUploadable()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
                ->disableOriginalConstructor()
                ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromEvent')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($class)
            ->will($this->returnValue(false));

        $this->adapter
            ->expects($this->never())
            ->method('recomputeChangeSet');

        $this->handler
            ->expects($this->never())
            ->method('handleUpload');

        $listener = new DoctrineUploaderListener($this->adapter, $this->metadata, $this->handler);
        $listener->preUpdate($args);
    }

    /**
     * Test the postLoad method.
     */
    public function testPostLoad()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
                ->disableOriginalConstructor()
                ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromEvent')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($class)
            ->will($this->returnValue(true));

        $this->handler
            ->expects($this->once())
            ->method('handleHydration')
            ->with($obj);

        $listener = new DoctrineUploaderListener($this->adapter, $this->metadata, $this->handler);
        $listener->postLoad($args);
    }

    /**
     * Test that postLoad skips non uploadable entity.
     */
    public function testPostLoadSkipsNonUploadable()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
                ->disableOriginalConstructor()
                ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromEvent')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($class)
            ->will($this->returnValue(false));

        $this->handler
            ->expects($this->never())
            ->method('handleHydration');

        $listener = new DoctrineUploaderListener($this->adapter, $this->metadata, $this->handler);
        $listener->postLoad($args);
    }

    /**
     * Test the postRemove method.
     */
    public function testPostRemove()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
                ->disableOriginalConstructor()
                ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromEvent')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($class)
            ->will($this->returnValue(true));

        $this->handler
            ->expects($this->once())
            ->method('handleDeletion')
            ->with($obj);

        $listener = new DoctrineUploaderListener($this->adapter, $this->metadata, $this->handler);
        $listener->postRemove($args);
    }

    /**
     * Test that postRemove skips non uploadable entity.
     */
    public function testPostRemoveSkipsNonUploadable()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
                ->disableOriginalConstructor()
                ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromEvent')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with($class)
            ->will($this->returnValue(false));

        $this->handler
            ->expects($this->never())
            ->method('handleDeletion');

        $listener = new DoctrineUploaderListener($this->adapter, $this->metadata, $this->handler);
        $listener->postRemove($args);
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
}
