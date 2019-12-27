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
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events
     */
    public function getSubscribedEvents(): array
    {
        return [
            'preRemove',
            'postRemove',
        ];
    }

    /**
     * Ensures a proxy will be usable in the postRemove.
     *
     * @param EventArgs $event The event
     */
    public function preRemove(EventArgs $event): void
    {
        $object = $this->adapter->getObjectFromArgs($event);

        if ($this->isUploadable($object) && $object instanceof Proxy) {
            $object->__load();
        }
    }

    /**
     * @param EventArgs $event The event
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function postRemove(EventArgs $event): void
    {
        $object = $this->adapter->getObjectFromArgs($event);

        if (!$this->isUploadable($object)) {
            return;
        }

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->remove($object, $field);
        }
    }
}
