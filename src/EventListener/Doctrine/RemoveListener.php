<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventArgs;
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
    private array $entities = [];

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
     * @param EventArgs $event The event
     */
    public function preRemove(EventArgs $event): void
    {
        $object = $this->adapter->getObjectFromArgs($event);

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
