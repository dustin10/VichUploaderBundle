<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\PostFlushEventArgs;

/**
 * FlushListener.
 *
 * Listen to the flush event.
 *
 * @author Kim Wüstkamp <kim@wuestkamp.com>
 */
final class FlushListener extends BaseListener
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events
     */
    public function getSubscribedEvents(): array
    {
        return [
            'postFlush',
        ];
    }

    /**
     * Initiates deletion of to-be-removed files in queue.
     *
     * @param PostFlushEventArgs $event The event
     */
    public function postFlush(PostFlushEventArgs $event): void
    {
        $em = $event->getEntityManager();
        $entities = $this->handler->removeFilesInQueue();

        if (count($entities) > 0) {
            $em->flush();
        }
    }
}
