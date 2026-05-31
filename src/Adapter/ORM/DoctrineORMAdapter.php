<?php

namespace Vich\UploaderBundle\Adapter\ORM;

use Doctrine\Persistence\Event\LifecycleEventArgs;
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
    public function recomputeChangeSet(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata($object::class);
        $uow->recomputeSingleEntityChangeSet($metadata, $object);
    }
}
