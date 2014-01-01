<?php

namespace Vich\UploaderBundle\EventListener\Propel;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * CleanListener
 *
 * Listen to the update event to delete old files accordingly.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class CleanListener extends BaseListener implements EventSubscriberInterface
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events.
     */
    public static function getSubscribedEvents()
    {
        return array(
            'propel.pre_update' => 'onUpload',
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
        $this->handler->clean($object, $this->mapping);
    }
}
