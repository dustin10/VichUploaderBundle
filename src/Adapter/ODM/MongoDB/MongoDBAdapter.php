<?php

namespace Vich\UploaderBundle\Adapter\ODM\MongoDB;

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
    public function getObjectFromArgs($event)
    {
        return $event->getDocument();
    }

    public function recomputeChangeSet($event): void
    {
        $object = $this->getObjectFromArgs($event);

        $dm = $event->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        $metadata = $dm->getClassMetadata(\get_class($object));
        $uow->recomputeSingleDocumentChangeSet($metadata, $object);
    }
}
