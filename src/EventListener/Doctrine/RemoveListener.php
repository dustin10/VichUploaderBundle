<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Proxy;

/**
 * RemoveListener.
 *
 * Listen to the remove event to delete files accordingly.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class RemoveListener extends BaseListener
{
    /** @var array */
    private $entities = [];

    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events
     */
    public function getSubscribedEvents(): array
    {
        return [
            'preRemove',
            'postFlush',
        ];
    }

    /**
     * Ensures a proxy will be usable in the postFlush (when transaction has ended).
     *
     * @param LifecycleEventArgs $event The event
     */
    public function preRemove(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        if ($this->isUploadable($object)) {
            if ($object instanceof Proxy) {
                $object->__load();
            }
            $this->entities[] = clone $object;
        }
    }

    public function postFlush(): void
    {
        foreach ($this->entities as $object) {
            foreach ($this->getUploadableFields($object) as $field) {
                $this->handler->remove($object, $field);
            }
        }
        $this->entities = [];
    }
}
