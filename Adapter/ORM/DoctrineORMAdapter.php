<?php

namespace Vich\UploaderBundle\Adapter\ORM;

use Vich\UploaderBundle\Adapter\AdapterInterface;

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
    public function getObjectFromArgs($event)
    {
        return $event->getEntity();
    }

    /**
     * {@inheritDoc}
     */
    public function recomputeChangeSet($event)
    {
        $object = $this->getObjectFromArgs($event);

        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata(get_class($object));
        $uow->recomputeSingleEntityChangeSet($metadata, $object);
    }
}
