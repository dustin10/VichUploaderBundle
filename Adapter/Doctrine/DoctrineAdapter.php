<?php

namespace Vich\UploaderBundle\Adapter\Doctrine;

use Doctrine\Common\Persistence\Proxy;

abstract class DoctrineAdapter
{
    /**
     * {@inheritDoc}
     */
    public function getClassName($object)
    {
        if ($object instanceof Proxy) {
            return get_parent_class($object);
        }

        return get_class($object);
    }
}
