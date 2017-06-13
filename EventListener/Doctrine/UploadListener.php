<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Handles file uploads.
 *
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadListener extends BaseListener
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'preFlush',
            'prePersist',
        ];
    }

    public function preFlush(EventArgs $event)
    {
        $identityMap = $this->adapter->getIdentityMapForEvent($event);

        foreach ($identityMap as $class => $entities) {
            $fields = $this->metadata->getUploadableFields($class);
            foreach ($fields as $field) {
                foreach ($entities as $entity) {
                    $this->handler->upload($entity, $field['propertyName']);
                }
            }
        }
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $object = $event->getObject();

        $fields = $this->getUploadableFields($object);
        foreach ($fields as $field) {
            $this->handler->upload($object, $field['propertyName']);
        }
    }
}
