<?php

namespace Vich\UploaderBundle\EventListener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Storage\StorageInterface;

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
     * @var \Vich\UploaderBundle\Metadata\MetadataReader $metadata
     */
    protected $metadata;

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
     * @param \Vich\UploaderBundle\Metadata\MetadataReader        $metadata The metadata reader.
     * @param \Vich\UploaderBundle\Storage\StorageInterface       $storage  The storage.
     * @param \Vich\UploaderBundle\Injector\FileInjectorInterface $injector The injector.
     */
    public function __construct(AdapterInterface $adapter, MetadataReader $metadata, StorageInterface $storage, FileInjectorInterface $injector)
    {
        $this->adapter = $adapter;
        $this->metadata = $metadata;
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
        $object = $this->adapter->getObjectFromArgs($args);

        if ($this->metadata->isUploadable($this->adapter->getClassName($object))) {
            $this->storage->upload($object);
            $this->injector->injectFiles($object);
        }
    }

    /**
     * Update the file and file name if necessary.
     *
     * @param EventArgs $args The event arguments.
     */
    public function preUpdate(EventArgs $args)
    {
        $object = $this->adapter->getObjectFromArgs($args);

        if ($this->metadata->isUploadable($this->adapter->getClassName($object))) {
            $this->storage->upload($object);
            $this->injector->injectFiles($object);
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
        $object = $this->adapter->getObjectFromArgs($args);

        if ($this->metadata->isUploadable($this->adapter->getClassName($object))) {
            $this->injector->injectFiles($object);
        }
    }

    /**
     * Removes the file if necessary.
     *
     * @param EventArgs $args The event arguments.
     */
    public function postRemove(EventArgs $args)
    {
        $object = $this->adapter->getObjectFromArgs($args);

        if ($this->metadata->isUploadable($this->adapter->getClassName($object))) {
            $this->storage->remove($object);
        }
    }
}
