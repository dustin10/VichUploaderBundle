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
    public static function getSubscribedEvents()
    {
        return array(
            'propel.post_delete' => 'onDelete',
        );
    }

    /**
     * Removes the file when the object is deleted.
     *
     * @param GenericEvent $event The event.
     */
    public function onDelete(GenericEvent $event)
    {
        $object = $this->adapter->getObjectFromEvent($event);
        $this->handler->delete($object, $this->mapping);
    }
}
