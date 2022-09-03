<?php

namespace Vich\UploaderBundle\Adapter\ORM;

use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * DoctrineORMAdapter.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 *
 * @internal
 */
final class DoctrineORMAdapter implements AdapterInterface
{
    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $event
     */
    public function getObjectFromArgs(object $event): object
    {
        return $event->getEntity();
    }

    /**
     * @param \Doctrine\ORM\Event\PreUpdateEventArgs $event
     */
    public function recomputeChangeSet(object $event): void
    {
        $object = $this->getObjectFromArgs($event);

        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata($object::class);
        $uow->recomputeSingleEntityChangeSet($metadata, $object);
    }
}
