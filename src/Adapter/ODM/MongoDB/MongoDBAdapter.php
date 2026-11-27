<?php

namespace Vich\UploaderBundle\Adapter\ODM\MongoDB;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs as BaseLifecycleEventArgs;
use Doctrine\Persistence\Event\ManagerEventArgs;
use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 *
 * @internal
 */
final readonly class MongoDBAdapter implements AdapterInterface
{
    /**
     * @param LifecycleEventArgs $event
     */
    public function recomputeChangeSet(BaseLifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        $dm = $event->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        $metadata = $dm->getClassMetadata($object::class);
        $uow->recomputeSingleDocumentChangeSet($metadata, $object);
    }

    public function getObjectsToFlush(ManagerEventArgs $event, bool $includeInsertions): iterable
    {
        return [];
    }
}
