<?php

namespace Vich\UploaderBundle\Adapter\ODM\MongoDB;

use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * MongoDBAdapter.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 *
 * @internal
 */
final class MongoDBAdapter implements AdapterInterface
{
    public function getObjectFromArgs(object $event): object
    {
        return $event->getDocument();
    }

    public function recomputeChangeSet(object $event): void
    {
        $object = $this->getObjectFromArgs($event);

        $dm = $event->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        $metadata = $dm->getClassMetadata($object::class);
        $uow->recomputeSingleDocumentChangeSet($metadata, $object);
    }
}
