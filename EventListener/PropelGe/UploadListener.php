<?php

namespace Vich\UploaderBundle\EventListener\PropelGe;

use Glorpen\Propel\PropelBundle\Events\ModelEvent;

/**
 * UploadListener
 *
 * Handles file uploads.
 *
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class UploadListener extends BaseListener
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events.
     */
    public static function getSubscribedEvents()
    {
        return array(
            'model.save.pre' => 'onUpload',
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
            $this->handler->upload($object, $field);
        }
    }
}
