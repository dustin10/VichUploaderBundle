<?php

namespace Vich\UploaderBundle\Adapter\Propel;

use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * Propel adapter.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class PropelORMAdapter implements AdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getObjectFromArgs($event)
    {
        return $event->getSubject();
    }

    /**
     * {@inheritDoc}
     */
    public function recomputeChangeSet($event)
    {
    }
}
