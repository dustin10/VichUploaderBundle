<?php

namespace Vich\UploaderBundle\Adapter\ORM;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Doctrine\Common\EventArgs;
use Doctrine\Common\Persistence\Proxy;

/**
 * DoctrineORMAdapter.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class DoctrineORMAdapter implements AdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getObjectFromArgs(EventArgs $e)
    {
        return $e->getEntity();
    }

    /**
     * {@inheritDoc}
     */
    public function recomputeChangeSet(EventArgs $e)
    {
        $obj = $this->getObjectFromArgs($e);

        $em = $e->getEntityManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata(get_class($obj));
        $uow->recomputeSingleEntityChangeSet($metadata, $obj);
    }

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
