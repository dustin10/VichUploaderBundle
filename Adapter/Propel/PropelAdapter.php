<?php

namespace Vich\UploaderBundle\Adapter\Propel;

use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * Propel adapter.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class PropelAdapter implements AdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getObjectFromEvent($event)
    {
        /* @var $event \Symfony\Component\EventDispatcher\GenericEvent */

        return $event->getSubject();
    }

    /**
     * {@inheritDoc}
     */
    public function recomputeChangeSet($event)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getReflectionClass($object)
    {
        return new \ReflectionClass($object);
    }
}
