<?php

namespace Vich\UploaderBundle\Adapter\ODM\MongoDB;

use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * MongoDBAdapter.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class MongoDBAdapter implements AdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getObjectFromArgs($event)
    {
        return $event->getDocument();
    }

    /**
     * {@inheritdoc}
     */
    public function recomputeChangeSet($event): void
    {
        $object = $this->getObjectFromArgs($event);

        $dm = $event->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        $metadata = $dm->getClassMetadata(\get_class($object));
        $uow->recomputeSingleDocumentChangeSet($metadata, $object);
    }
}
