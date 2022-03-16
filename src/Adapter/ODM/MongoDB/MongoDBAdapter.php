<?php

namespace Vich\UploaderBundle\Adapter\ODM\MongoDB;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs as BaseLifecycleEventArgs;
use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * MongoDBAdapter.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 * @final
 *
 * @internal
 */
class MongoDBAdapter implements AdapterInterface
{
    /**
     * @param LifecycleEventArgs $event
     */
    public function recomputeChangeSet(BaseLifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        $dm = $event->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        $metadata = $dm->getClassMetadata(\get_class($object));
        $uow->recomputeSingleDocumentChangeSet($metadata, $object);
    }

    /**
     * @param PreUpdateEventArgs $event
     */
    public function getChangeSet(BaseLifecycleEventArgs $event): array
    {
        return $event->getDocumentChangeSet();
    }
}
