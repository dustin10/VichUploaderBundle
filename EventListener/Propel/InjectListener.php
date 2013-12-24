<?php

namespace Vich\UploaderBundle\EventListener\Propel;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * InjectListener
 *
 * Listen to the load event in order to inject File objects.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class InjectListener extends BaseListener implements EventSubscriberInterface
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events.
     */
    public function getSubscribedEvents()
    {
        return array(
            'propel.post_hydrate' => 'onHydrate',
        );
    }

    /**
     * Populates uploadable fields from filename properties.
     *
     * @param GenericEvent $event The event.
     */
    public function onHydrate(GenericEvent $event)
    {
        $object = $this->adapter->getObjectFromEvent($event);
        $this->handler->handleHydration($object, $this->mapping);
    }
}
