<?php

namespace Vich\UploaderBundle\Adapter\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs as BaseLifecycleEventArgs;
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
     * @param LifecycleEventArgs $event
     */
    public function recomputeChangeSet(BaseLifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata($object::class);
        $uow->recomputeSingleEntityChangeSet($metadata, $object);
    }
}
