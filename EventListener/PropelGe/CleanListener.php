<?php

namespace Vich\UploaderBundle\EventListener\PropelGe;

use Glorpen\Propel\PropelBundle\Events\ModelEvent;

/**
 * CleanListener
 *
 * Listen to the update event to delete old files accordingly.
 *
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class CleanListener extends BaseListener
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events.
     */
    public static function getSubscribedEvents()
    {
        return array(
            'model.update.pre' => 'onUpload',
        );
    }

    /**
     * @param GenericEvent $event The event.
     */
    public function onUpload(ModelEvent $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);

        if (!$this->isUploadable($object)) {
            return;
        }

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->clean($object, $field);
        }
    }
}
