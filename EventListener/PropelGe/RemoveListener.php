<?php

namespace Vich\UploaderBundle\EventListener\PropelGe;

use Glorpen\Propel\PropelBundle\Events\ModelEvent;

/**
 * RemoveListener
 *
 * Listen to the remove event to delete files accordingly.
 *
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class RemoveListener extends BaseListener
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events.
     */
    public static function getSubscribedEvents()
    {
        return array(
            'model.delete.post' => 'onDelete',
        );
    }

    /**
     * @param GenericEvent $event The event.
     */
    public function onDelete(ModelEvent $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);

        if (!$this->isUploadable($object)) {
            return;
        }

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->remove($object, $field);
        }
    }
}
