<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\ManagerEventArgs;

/**
 * CleanListener.
 *
 * Listen to the update event to delete old files accordingly.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class CleanListener extends BaseListener
{
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

    public function preFlush(ManagerEventArgs $event): void
    {
        foreach ($this->getUploadableObjectsToFlush($event, false) as $object) {
            foreach ($this->getUploadableFields($object) as $field) {
                $this->handler->clean($object, $field);
            }
        }
    }
}
