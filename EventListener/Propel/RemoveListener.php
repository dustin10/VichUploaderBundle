<?php

namespace Vich\UploaderBundle\EventListener\Propel;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * RemoveListener.
 *
 * Listen to the remove event to delete files accordingly.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class RemoveListener extends BaseListener
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events
     */
    public static function getSubscribedEvents()
    {
        return [
            'propel.post_delete' => 'onDelete',
        ];
    }

    /**
     * @param GenericEvent $event The event
     */
    public function onDelete(GenericEvent $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->remove($object, $field);
        }
    }
}
