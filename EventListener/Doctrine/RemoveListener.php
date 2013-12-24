<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;

/**
 * RemoveListener
 *
 * Listen to the remove event to delete files accordingly.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class RemoveListener extends BaseListener implements EventSubscriber
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events.
     */
    public function getSubscribedEvents()
    {
        return array(
            'preUpdate',
            'postRemove',
        );
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
            $this->handler->handleCleaning($object, $this->mapping);
            $this->adapter->recomputeChangeSet($event);
        }
    }

    /**
     * Removes the file if necessary.
     *
     * @param EventArgs $event The event.
     */
    public function postRemove(EventArgs $event)
    {
        $object = $this->adapter->getObjectFromEvent($event);

        if ($this->metadata->isUploadable($this->adapter->getClassName($object))) {
            $this->handler->handleDeletion($object, $this->mapping);
        }
    }
}
