<?php

namespace Vich\UploaderBundle\EventListener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Proxy;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Util\ClassUtils;

/**
 * DoctrineUploaderListener.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class DoctrineUploaderListener implements EventSubscriber
{
    /**
     * @var AdapterInterface $adapter
     */
    protected $adapter;

    /**
     * @var MetadataReader $metadata
     */
    protected $metadata;

    /**
     * @var UploadHandler $handler
     */
    protected $handler;

    /**
     * @var string $mapping
     */
    protected $mapping;

    /**
     * Constructs a new instance of UploaderListener.
     *
     * @param string           $mapping  The mapping name.
     * @param AdapterInterface $adapter  The adapter.
     * @param MetadataReader   $metadata The metadata reader.
     * @param UploadHandler    $handler  The upload handler.
     */
    public function __construct($mapping, AdapterInterface $adapter, MetadataReader $metadata, UploadHandler $handler)
    {
        $this->mapping = $mapping;
        $this->adapter = $adapter;
        $this->metadata = $metadata;
        $this->handler = $handler;
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
            'preRemove',
            'postRemove',
        );
    }

    /**
     * Checks for file to upload.
     *
     * @param EventArgs $event The event.
     */
    public function prePersist(EventArgs $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);

        if ($this->isUploadable($object)) {
            $this->handler->handleUpload($object, $this->mapping);
        }
    }

    /**
     * Update the file and file name if necessary.
     *
     * @param EventArgs $event The event.
     */
    public function preUpdate(EventArgs $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);

        if ($this->isUploadable($object)) {
            $this->handler->handleUpload($object, $this->mapping);
            $this->adapter->recomputeChangeSet($event);
        }
    }

    /**
     * Populates uploadable fields from filename properties.
     *
     * @param EventArgs $event The event.
     */
    public function postLoad(EventArgs $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);

        if ($this->isUploadable($object)) {
            $this->handler->handleHydration($object, $this->mapping);
        }
    }

    /**
     * Ensures a proxy will be usable in the postRemove.
     *
     * @param EventArgs $event The event.
     */
    public function preRemove(EventArgs $event)
    {
         $object = $this->adapter->getObjectFromArgs($event);

         if ($this->isUploadable($object) && $object instanceof Proxy) {
             $object->__load();
         }
    }

    /**
     * Removes the file if necessary.
     *
     * @param EventArgs $event The event.
     */
    public function postRemove(EventArgs $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);

        if ($this->isUploadable($object)) {
            $this->handler->handleDeletion($object, $this->mapping);
        }
    }

    /**
     * Tells if the given object is uploadable.
     *
     * @param object $object The object.
     *
     * @return bool
     */
    private function isUploadable($object)
    {
        return $this->metadata->isUploadable(ClassUtils::getClass($object));
    }
}
