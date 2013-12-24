<?php

namespace Vich\UploaderBundle\EventListener\Propel;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * RemoveListener
 *
 * Listen to the remove event to delete files accordingly.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class RemoveListener extends BaseListener implements EventSubscriberInterface
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events.
     */
    public function getSubscribedEvents()
    {
        return array(
            'propel.pre_update'     => 'onUpload',
            'propel.post_delete'    => 'onDelete',
        );
    }

    /**
     * Update the file and file name if necessary.
     *
     * @param GenericEvent $event The event.
     */
    public function onUpload(GenericEvent $event)
    {
        $object = $this->adapter->getObjectFromEvent($event);
        $this->handler->handleCleaning($object, $this->mapping);
    }

    /**
     * Removes the file when the object is deleted.
     *
     * @param GenericEvent $event The event.
     */
    public function onDelete(GenericEvent $event)
    {
        $object = $this->adapter->getObjectFromEvent($event);
        $this->handler->handleDeletion($object, $this->mapping);
    }
}
