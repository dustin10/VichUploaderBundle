<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * UploadListener.
 *
 * Handles file uploads.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadListener extends BaseListener
{
    /**
     * @param LifecycleEventArgs $event The event
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function prePersist(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        if (!$this->isUploadable($object)) {
            return;
        }

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->upload($object, $field);
        }
    }

    /**
     * @param LifecycleEventArgs $event The event
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function preUpdate(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        if (!$this->isUploadable($object)) {
            return;
        }

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->upload($object, $field);
        }

        $this->adapter->recomputeChangeSet($event);
    }
}
