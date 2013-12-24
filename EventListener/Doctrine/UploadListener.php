<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;

/**
 * UploadListener
 *
 * Handles file uploads.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadListener extends BaseListener implements EventSubscriber
{
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
        );
    }

    /**
     * Checks for file to upload.
     *
     * @param EventArgs $event The event.
     */
    public function prePersist(EventArgs $event)
    {
        $object = $this->adapter->getObjectFromEvent($event);

        if ($this->metadata->isUploadable($this->adapter->getClassName($object))) {
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
        $object = $this->adapter->getObjectFromEvent($event);

        if ($this->metadata->isUploadable($this->adapter->getClassName($object))) {
            $this->handler->handleUpload($object, $this->mapping);
            $this->adapter->recomputeChangeSet($event);
        }
    }
}
