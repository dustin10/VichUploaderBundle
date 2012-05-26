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
     * @var \Vich\UploaderBundle\Driver\AnnotationDriver $driver
     */
    protected $driver;

    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface $storage
     */
    protected $storage;

    /**
     * @var \Vich\UploaderBundle\Injector\FileInjectorInterface $injector
     */
    protected $injector;

    /**
     * Sets up the test
     */
    public function setUp()
    {
        $this->adapter = $this->getAdapterMock();
        $this->driver = $this->getDriverMock();
        $this->storage = $this->getStorageMock();
        $this->injector = $this->getInjectorMock();
    }

    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents()
    {
        $listener = new UploaderListener($this->adapter, $this->driver, $this->storage, $this->injector);
        $events = $listener->getSubscribedEvents();

        $this->assertTrue(in_array('postUpdate', $events));
        $this->assertTrue(in_array('postLoad', $events));
        $this->assertTrue(in_array('postPersist', $events));
        $this->assertTrue(in_array('postRemove', $events));
    }

    /**
     * Tests the prePersist method.
     */
    public function testPostPersist()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
                ->disableOriginalConstructor()
                ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $uploadable = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\Uploadable')
                      ->disableOriginalConstructor()
                      ->getMock();

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->storage
            ->expects($this->once())
            ->method('upload')
            ->with($obj);

        $listener = new UploaderListener($this->adapter, $this->driver, $this->storage, $this->injector);
        $listener->postPersist($args);
    }

    /**
     * Tests that prePersist skips non-uploadable entity.
     */
    public function testPostPersistSkipsNonUploadable()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
                ->disableOriginalConstructor()
                ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue(null));

        $this->storage
            ->expects($this->never())
            ->method('upload');

        $listener = new UploaderListener($this->adapter, $this->driver, $this->storage, $this->injector);
        $listener->postPersist($args);
    }

    /**
     * Test the preUpdate method.
     */
    public function testPostUpdate()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
                ->disableOriginalConstructor()
                ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->adapter
            ->expects($this->once())
            ->method('update')
            ->with($args);

        $uploadable = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\Uploadable')
                      ->disableOriginalConstructor()
                      ->getMock();

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->storage
            ->expects($this->once())
            ->method('upload')
            ->with($obj);

        $listener = new UploaderListener($this->adapter, $this->driver, $this->storage, $this->injector);
        $listener->postUpdate($args);
    }

    /**
     * Test that preUpdate skips non uploadable entity.
     */
    public function testPostUpdateSkipsNonUploadable()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $args = $this->getMockBuilder('Doctrine\Common\EventArgs')
                ->disableOriginalConstructor()
                ->getMock();

        $this->adapter
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue(null));

        $this->storage
            ->expects($this->never())
            ->method('upload');

        $this->adapter
            ->expects($this->never())
            ->method('update');

        $listener = new UploaderListener($this->adapter, $this->driver, $this->storage, $this->injector);
        $listener->postUpdate($args);
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
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $uploadable = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\Uploadable')
                      ->disableOriginalConstructor()
                      ->getMock();

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->injector
            ->expects($this->once())
            ->method('injectFiles')
            ->with($obj);

        $listener = new UploaderListener($this->adapter, $this->driver, $this->storage, $this->injector);
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
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue(null));

        $this->injector
            ->expects($this->never())
            ->method('injectFiles');

        $listener = new UploaderListener($this->adapter, $this->driver, $this->storage, $this->injector);
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
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $uploadable = $this->getMockBuilder('Vich\UploaderBundle\Mapping\Annotation\Uploadable')
                      ->disableOriginalConstructor()
                      ->getMock();

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($obj);

        $listener = new UploaderListener($this->adapter, $this->driver, $this->storage, $this->injector);
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
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->adapter
            ->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue($class));

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue(null));

        $this->storage
            ->expects($this->never())
            ->method('remove');

        $listener = new UploaderListener($this->adapter, $this->driver, $this->storage, $this->injector);
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
     * Creates a mock annotation driver.
     *
     * @return \Vich\UploaderBundle\Driver\AnnotationDriver The mock driver.
     */
    protected function getDriverMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Driver\AnnotationDriver')
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
