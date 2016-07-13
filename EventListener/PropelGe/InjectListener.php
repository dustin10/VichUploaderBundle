<?php

namespace Vich\UploaderBundle\EventListener\PropelGe;

use Glorpen\Propel\PropelBundle\Events\ModelEvent;

/**
 * InjectListener
 *
 * Listen to the load event in order to inject File objects.
 *
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class InjectListener extends BaseListener
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events.
     */
    public static function getSubscribedEvents()
    {
        return array(
            'model.hydrate.post' => 'onHydrate',
        );
    }

    /**
     * @param GenericEvent $event The event.
     */
    public function onHydrate(ModelEvent $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);

        if (!$this->isUploadable($object)) {
            return;
        }

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->inject($object, $field);
        }
    }
}
