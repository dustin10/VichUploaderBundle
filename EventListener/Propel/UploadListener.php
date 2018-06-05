<?php

namespace Vich\UploaderBundle\EventListener\Propel;

use Symfony\Component\EventDispatcher\GenericEvent;

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
     * The events the listener is subscribed to.
     *
     * @return array The array of events
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'propel.pre_insert' => 'onUpload',
            'propel.pre_update' => 'onUpload',
        ];
    }

    /**
     * @param GenericEvent $event The event
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function onUpload(GenericEvent $event): void
    {
        $object = $this->adapter->getObjectFromArgs($event);

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->upload($object, $field);
        }
    }
}
