<?php

namespace Vich\UploaderBundle\Tests\EventListener;

use Vich\UploaderBundle\EventListener\UploaderListener;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * UploaderListenerTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploaderListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vich\UploaderBundle\Adapter\AdapterInterface $adapter
     */
    protected $adapter;

    /**
     * @var \Vich\UploaderBundle\Mapping\MappingReader $mapping
     */
    protected $mapping;

    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface $storage
     */
    protected $storage;

    /**
     * @var \Vich\UploaderBundle\Injector\FileInjectorInterface $injector
     */
    protected $injector;

    /**
     * @var \Vich\UploaderBundle\EventListener\UploaderListener $listener
     */
    protected $listener;

    /**
     * Sets up the test
     */
    public function setUp()
    {
        $this->adapter = $this->getAdapterMock();
        $this->mapping = $this->getMappingMock();
        $this->storage = $this->getStorageMock();
        $this->injector = $this->getInjectorMock();

        $this->listener = new UploaderListener($this->adapter, $this->mapping, $this->storage, $this->injector);
    }

    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents()
    {
        $events = $this->listener->getSubscribedEvents();

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

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->mapping
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->storage
            ->expects($this->once())
            ->method('upload')
            ->with($obj);

        $this->injector
            ->expects($this->once())
            ->method('injectFiles')
            ->with($obj);

        $this->listener->prePersist($args);
    }

    /**
     * Tests that prePersist skips non-uploadable entity.
     */
    public function testPrePersistSkipsNonUploadable()
    {
        $obj = new DummyEntity();

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->mapping
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(false));

        $this->storage
            ->expects($this->never())
            ->method('upload');

        $this->injector
            ->expects($this->never())
            ->method('injectFiles');

        $this->listener->prePersist($args);
    }

    /**
     * Test the preUpdate method.
     */
    public function testPreUpdate()
    {
        $obj = new DummyEntity();

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->adapter
            ->expects($this->once())
            ->method('recomputeChangeSet')
            ->with($args);

        $this->mapping
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->storage
            ->expects($this->once())
            ->method('upload')
            ->with($obj);

        $this->injector
            ->expects($this->once())
            ->method('injectFiles')
            ->with($obj);

        $this->listener->preUpdate($args);
    }

    /**
     * Test that preUpdate skips non uploadable entity.
     */
    public function testPreUpdateSkipsNonUploadable()
    {
        $obj = new DummyEntity();

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->mapping
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(false));

        $this->storage
            ->expects($this->never())
            ->method('upload');

        $this->adapter
            ->expects($this->never())
            ->method('recomputeChangeSet');

        $this->injector
            ->expects($this->never())
            ->method('injectFiles');

        $this->listener->preUpdate($args);
    }

    /**
     * Test the postLoad method.
     */
    public function testPostLoad()
    {
        $obj = new DummyEntity();

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->mapping
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->injector
            ->expects($this->once())
            ->method('injectFiles')
            ->with($obj);

        $this->listener->postLoad($args);
    }

    /**
     * Test that postLoad skips non uploadable entity.
     */
    public function testPostLoadSkipsNonUploadable()
    {
        $obj = new DummyEntity();

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->mapping
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(false));

        $this->injector
            ->expects($this->never())
            ->method('injectFiles');

        $this->listener->postLoad($args);
    }

    /**
     * Test the postRemove method.
     */
    public function testPostRemove()
    {
        $obj = new DummyEntity();

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->mapping
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($obj);

        $this->listener->postRemove($args);
    }

    /**
     * Test that postRemove skips non uploadable entity.
     */
    public function testPostRemoveSkipsNonUploadable()
    {
        $obj = new DummyEntity();

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('Vich\UploaderBundle\Tests\DummyEntity'));

        $this->mapping
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(false));

        $this->storage
            ->expects($this->never())
            ->method('remove');

        $this->listener->postRemove($args);
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
     * Creates a mock mapping reader.
     *
     * @return \Vich\UploaderBundle\Mapping\MappingReader The mock mapping reader.
     */
    protected function getMappingMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\MappingReader')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock storage.
     *
     * @return \Vich\UploaderBundle\Storage\StorageInterface The mock storage.
     */
    protected function getStorageMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Storage\StorageInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock injector.
     *
     * @return \Vich\UploaderBundle\Injector\FileInjectorInterface The mock injector.
     */
    protected function getInjectorMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Injector\FileInjectorInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
