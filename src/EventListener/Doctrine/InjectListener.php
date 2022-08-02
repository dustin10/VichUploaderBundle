<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * InjectListener.
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
     * @return array The array of events
     */
    public function getSubscribedEvents(): array
    {
        return [
            'postLoad',
        ];
    }

    /**
     * @param LifecycleEventArgs $event The event
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function postLoad(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        if (!$this->isUploadable($object)) {
            return;
        }

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->inject($object, $field);
        }
    }
}
