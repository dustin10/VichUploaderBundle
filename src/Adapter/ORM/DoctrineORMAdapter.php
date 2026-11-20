<?php

namespace Vich\UploaderBundle\Adapter\ORM;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\ManagerEventArgs;
use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * DoctrineORMAdapter.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 *
 * @internal
 */
final readonly class DoctrineORMAdapter implements AdapterInterface
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

    public function getObjectsToFlush(ManagerEventArgs $event, bool $includeInsertions): iterable
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();
        if ($includeInsertions) {
            yield from $uow->getScheduledEntityInsertions();
        }

        // mirrors the entities that UnitOfWork::computeChangeSets() looks at
        foreach ($uow->getIdentityMap() as $className => $entities) {
            $metadata = $em->getClassMetadata($className);

            if ($metadata->isReadOnly) {
                continue;
            }

            foreach ($entities as $entity) {
                if ($uow->isScheduledForInsert($entity)
                    || $uow->isUninitializedObject($entity)
                    || $uow->isReadOnly($entity)
                    || $uow->isScheduledForDelete($entity)
                    || (!$metadata->isChangeTrackingDeferredImplicit() && !$uow->isScheduledForDirtyCheck($entity))
                ) {
                    continue;
                }

                yield $entity;
            }
        }
    }
}
