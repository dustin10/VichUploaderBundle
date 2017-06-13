<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\Common\Persistence\Proxy;

/**
 * Listen to the remove event to delete files accordingly.
 *
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class RemoveListener extends BaseListener
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'preRemove',
            'postRemove',
        ];
    }

    public function preRemove(EventArgs $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);
        // Ensures a proxy will be usable in the postRemove.
        if ($object instanceof Proxy) {
            $fields = $this->getUploadableFields($object);
            if ($fields) {
                $object->__load();
            }
        }
    }

    public function postRemove(EventArgs $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);
        foreach ($this->getUploadableFields($object) as $field) {
            if (!$this->isFlagEnabledForField('delete_on_remove', $field)) {
                continue;
            }

            $this->handler->remove($object, $field['propertyName']);
        }
    }
}
