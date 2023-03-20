<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * CleanListener.
 *
 * Listen to the update event to delete old files accordingly.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class CleanListener extends BaseListener
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array<int, string> The array of events
     */
    public function getSubscribedEvents(): array
    {
        return [
            'preUpdate',
        ];
    }

    public function preUpdate(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        if (!$this->isUploadable($object)) {
            return;
        }

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->clean($object, $field);
        }

        $this->adapter->recomputeChangeSet($event);
    }
}
