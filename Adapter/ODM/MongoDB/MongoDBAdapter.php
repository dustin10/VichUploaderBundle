<?php

namespace Vich\UploaderBundle\Adapter\ODM\MongoDB;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Adapter\Doctrine\DoctrineAdapter;

/**
 * MongoDBAdapter.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class MongoDBAdapter extends DoctrineAdapter implements AdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getObjectFromArgs($event)
    {
        return $event->getDocument();
    }

    /**
     * {@inheritDoc}
     */
    public function recomputeChangeSet($event)
    {
        $object = $this->getObjectFromArgs($event);

        $dm = $event->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        $metadata = $dm->getClassMetadata(get_class($object));
        $uow->recomputeSingleDocumentChangeSet($metadata, $object);
    }

}
