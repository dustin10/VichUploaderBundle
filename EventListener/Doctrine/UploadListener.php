<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventArgs;

/**
 * UploadListener
 *
 * Handles file uploads.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadListener extends BaseListener
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
        $object = $this->adapter->getObjectFromArgs($event);

        if ($this->isUploadable($object)) {
            $this->handler->upload($object, $this->mapping);
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
            $this->handler->upload($object, $this->mapping);
            $this->adapter->recomputeChangeSet($event);
        }
    }
}
