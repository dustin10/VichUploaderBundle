<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventArgs;

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

    public function preUpdate(EventArgs|\Doctrine\ORM\Event\PreUpdateEventArgs $event): void
    {
        $object = $this->adapter->getObjectFromArgs($event);

        if (!$this->isUploadable($object)) {
            return;
        }

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->clean($object, $field);
        }

        $this->adapter->recomputeChangeSet($event);
    }
}
