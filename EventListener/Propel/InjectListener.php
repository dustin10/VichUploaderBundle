<?php

namespace Vich\UploaderBundle\EventListener\Propel;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * InjectListener
 *
 * Listen to the load event in order to inject File objects.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
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
            'propel.post_hydrate' => 'onHydrate',
        );
    }

    /**
     * @param GenericEvent $event The event.
     */
    public function onHydrate(GenericEvent $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->inject($object, $field);
        }
    }
}
