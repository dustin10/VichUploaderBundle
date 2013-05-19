<?php

namespace Vich\UploaderBundle\EventListener;

use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Driver\AnnotationDriver;

abstract class AbstractListener
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var AnnotationDriver
     */
    protected $driver;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var FileInjectorInterface
     */
    protected $injector;

    /**
     * Constructs a new instance of UploaderListener.
     *
     * @param AdapterInterface      $adapter  The adapter.
     * @param AnnotationDriver      $driver   The driver.
     * @param StorageInterface      $storage  The storage.
     * @param FileInjectorInterface $injector The injector.
     */
    public function __construct(AdapterInterface $adapter, AnnotationDriver $driver, StorageInterface $storage, FileInjectorInterface $injector)
    {
        $this->adapter = $adapter;
        $this->driver = $driver;
        $this->storage = $storage;
        $this->injector = $injector;
    }

    /**
     * Tests if the object is Uploadable.
     *
     * @param  object  $obj The object.
     * @return boolean True if uploadable, false otherwise.
     */
    protected function isUploadable($obj)
    {
        $class = $this->adapter->getReflectionClass($obj);

        return null !== $this->driver->readUploadable($class);
    }    
}