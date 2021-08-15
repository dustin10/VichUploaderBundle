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

    /**
     * @param EventArgs|\Doctrine\ORM\Event\PreUpdateEventArgs $event
     */
    public function preUpdate(EventArgs $event): void
    {
        $object = $this->adapter->getObjectFromArgs($event);

        if (!$this->isUploadable($object)) {
            return;
        }

        $changeSet = $event->getEntityChangeSet();

        foreach ($this->getUploadableFilenameFields($object) as $field => $fileName) {
            if (!isset($changeSet[$fileName])) {
                continue;
            }

            $this->handler->clean($object, $field, $changeSet[$fileName][0]);
        }

        $this->adapter->recomputeChangeSet($event);
    }
}
