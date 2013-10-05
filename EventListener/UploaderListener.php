<?php

namespace Vich\UploaderBundle\EventListener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Mapping\MappingReader;

/**
 * UploaderListener.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploaderListener implements EventSubscriber
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
     * Constructs a new instance of UploaderListener.
     *
     * @param \Vich\UploaderBundle\Adapter\AdapterInterface       $adapter  The adapter.
     * @param \Vich\UploaderBundle\Mapping\MappingReader          $mapping  The mapping reader.
     * @param \Vich\UploaderBundle\Storage\StorageInterface       $storage  The storage.
     * @param \Vich\UploaderBundle\Injector\FileInjectorInterface $injector The injector.
     */
    public function __construct(AdapterInterface $adapter, MappingReader $mapping, StorageInterface $storage, FileInjectorInterface $injector)
    {
        $this->adapter = $adapter;
        $this->mapping = $mapping;
        $this->storage = $storage;
        $this->injector = $injector;
    }

    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events.
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
            'postLoad',
            'postRemove',
        );
    }

    /**
     * Checks for file to upload.
     *
     * @param \Doctrine\Common\EventArgs $args The event arguments.
     */
    public function prePersist(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);

        if ($this->mapping->isUploadable($this->adapter->getReflectionClass($obj))) {
            $this->storage->upload($obj);
            $this->injector->injectFiles($obj);
        }
    }

    /**
     * Update the file and file name if necessary.
     *
     * @param EventArgs $args The event arguments.
     */
    public function preUpdate(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);

        if ($this->mapping->isUploadable($this->adapter->getReflectionClass($obj))) {
            $this->storage->upload($obj);
            $this->injector->injectFiles($obj);
            $this->adapter->recomputeChangeSet($args);
        }
    }

    /**
     * Populates uploadable fields from filename properties
     * if necessary.
     *
     * @param \Doctrine\Common\EventArgs $args
     */
    public function postLoad(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);

        if ($this->mapping->isUploadable($this->adapter->getReflectionClass($obj))) {
            $this->injector->injectFiles($obj);
        }
    }

    /**
     * Removes the file if necessary.
     *
     * @param EventArgs $args The event arguments.
     */
    public function postRemove(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);

        if ($this->mapping->isUploadable($this->adapter->getReflectionClass($obj))) {
            $this->storage->remove($obj);
        }
    }
}
