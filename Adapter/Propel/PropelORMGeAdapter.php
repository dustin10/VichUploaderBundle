<?php

namespace Vich\UploaderBundle\Adapter\Propel;

use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * Propel adapter for GlorpenPropelBundle.
 *
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class PropelORMGeAdapter implements AdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getObjectFromArgs($event)
    {
        return $event->getModel();
    }

    /**
     * {@inheritDoc}
     */
    public function recomputeChangeSet($event)
    {
    }
}
