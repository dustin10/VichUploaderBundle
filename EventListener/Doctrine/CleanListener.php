<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventArgs;

/**
 * Listen to the update event to delete old files accordingly.
 *
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class CleanListener extends BaseListener
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'preFlush',
        ];
    }

    public function preFlush(EventArgs $event)
    {
        $identityMap = $this->adapter->getIdentityMapForEvent($event);

        foreach ($identityMap as $class => $entities) {
            $fields = $this->metadata->getUploadableFields($class);
            foreach ($fields as $field) {
                if (!$this->isFlagEnabledForField('delete_on_update', $field)) {
                    continue;
                }

                foreach ($entities as $entity) {
                    $this->handler->clean($entity, $field['propertyName']);
                }
            }
        }
    }
}
