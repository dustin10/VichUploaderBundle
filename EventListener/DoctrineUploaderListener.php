<?php

namespace Vich\UploaderBundle\EventListener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Metadata\MetadataReader;

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
     * @var UploaderHandler $handler
     */
    protected $handler;

    /**
     * Constructs a new instance of UploaderListener.
     *
     * @param AdapterInterface $adapter  The adapter.
     * @param MetadataReader   $metadata The metadata reader.
     * @param UploaderHandler  $handler  The upload handler.
     */
    public function __construct(AdapterInterface $adapter, MetadataReader $metadata, UploadHandler $handler)
    {
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
        $obj = $this->adapter->getObjectFromEvent($event);

        if ($this->metadata->isUploadable($this->adapter->getClassName($obj))) {
            $this->handler->handleUpload($obj);
        }
    }

    /**
     * Update the file and file name if necessary.
     *
     * @param EventArgs $event The event.
     */
    public function preUpdate(EventArgs $event)
    {
        $obj = $this->adapter->getObjectFromEvent($event);

        if ($this->metadata->isUploadable($this->adapter->getClassName($obj))) {
            $this->handler->handleUpload($obj);
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
        $obj = $this->adapter->getObjectFromEvent($event);

        if ($this->metadata->isUploadable($this->adapter->getClassName($obj))) {
            $this->handler->handleHydration($obj);
        }
    }

    /**
     * Removes the file if necessary.
     *
     * @param EventArgs $event The event.
     */
    public function postRemove(EventArgs $event)
    {
        $obj = $this->adapter->getObjectFromEvent($event);

        if ($this->metadata->isUploadable($this->adapter->getClassName($obj))) {
            $this->handler->handleDeletion($obj);
        }
    }
}
