<?php

namespace Vich\UploaderBundle\EventListener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Mapping\MappingReader;

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
     * @var MappingReader $mapping
     */
    protected $mapping;

    /**
     * @var UploaderHandler $handler
     */
    protected $handler;

    /**
     * Constructs a new instance of UploaderListener.
     *
     * @param AdapterInterface $adapter The adapter.
     * @param MappingReader    $mapping The mapping reader.
     * @param UploaderHandler  $handler The upload handler.
     */
    public function __construct(AdapterInterface $adapter, MappingReader $mapping, UploadHandler $handler)
    {
        $this->adapter = $adapter;
        $this->mapping = $mapping;
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

        if ($this->mapping->isUploadable($this->adapter->getReflectionClass($obj))) {
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

        if ($this->mapping->isUploadable($this->adapter->getReflectionClass($obj))) {
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

        if ($this->mapping->isUploadable($this->adapter->getReflectionClass($obj))) {
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

        if ($this->mapping->isUploadable($this->adapter->getReflectionClass($obj))) {
            $this->handler->handleDeletion($obj);
        }
    }
}
