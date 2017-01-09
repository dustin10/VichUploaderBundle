<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventArgs;

/**
 * InjectListener.
 *
 * Listen to the load event in order to inject File objects.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class InjectListener extends BaseListener
{
    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events
     */
    public function getSubscribedEvents()
    {
        return [
            'postLoad',
        ];
    }

    /**
     * @param EventArgs $event The event
     */
    public function postLoad(EventArgs $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);

        if (!$this->isUploadable($object)) {
            return;
        }

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->inject($object, $field);
        }
    }
}
