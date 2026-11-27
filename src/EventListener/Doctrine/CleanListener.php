<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\ManagerEventArgs;
use Doctrine\Persistence\ObjectManager;
use Vich\UploaderBundle\Handler\DeferredCleanHandlerInterface;
use Vich\UploaderBundle\Handler\PendingClean;

/**
 * CleanListener.
 *
 * Listen to the update event to delete old files accordingly.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class CleanListener extends BaseListener
{
    /** @var \WeakMap<ObjectManager, list<PendingClean>> */
    private \WeakMap $pendingCleans;

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
        if (!$this->handler instanceof DeferredCleanHandlerInterface) {
            foreach ($this->getUploadableObjectsToFlush($event, false) as $object) {
                foreach ($this->getUploadableFields($object) as $field) {
                    $this->handler->clean($object, $field);
                }
            }

            return;
        }

        $pendingCleans = [];

        foreach ($this->getUploadableObjectsToFlush($event, false) as $object) {
            foreach ($this->getUploadableFields($object) as $field) {
                $pendingClean = $this->handler->prepareClean($object, $field);

                if (null !== $pendingClean) {
                    $pendingCleans[] = $pendingClean;
                }
            }
        }

        $this->pendingCleans ??= new \WeakMap();
        $this->pendingCleans[$event->getObjectManager()] = $pendingCleans;
    }

    public function postFlush(ManagerEventArgs $event): void
    {
        if (!$this->handler instanceof DeferredCleanHandlerInterface) {
            return;
        }

        $manager = $event->getObjectManager();

        if (!isset($this->pendingCleans[$manager])) {
            return;
        }

        $pendingCleans = $this->pendingCleans[$manager];
        unset($this->pendingCleans[$manager]);

        foreach ($pendingCleans as $pendingClean) {
            $this->handler->completeClean($pendingClean);
        }
    }
}
