<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventArgs;

/**
 * Listen to the load event in order to inject File objects.
 *
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class InjectListener extends BaseListener
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postLoad',
        ];
    }

    public function postLoad(EventArgs $event)
    {
        $object = $this->adapter->getObjectFromArgs($event);
        foreach ($this->getUploadableFields($object) as $field) {
            if (!$this->isFlagEnabledForField('inject_on_load', $field)) {
                continue;
            }

            $this->handler->inject($object, $field['propertyName']);
        }
    }
}
