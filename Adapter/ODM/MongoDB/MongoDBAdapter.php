<?php

namespace Vich\UploaderBundle\Adapter\ODM\MongoDB;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Doctrine\Common\EventArgs;
use Doctrine\ODM\MongoDB\Proxy\Proxy;

/**
 * MongoDBAdapter.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class MongoDBAdapter implements AdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getObjectFromArgs(EventArgs $e)
    {
        return $e->getDocument();
    }

    /**
     * {@inheritDoc}
     */
    public function recomputeChangeSet(EventArgs $e)
    {
        $obj = $this->getObjectFromArgs($e);

        $dm = $e->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        $metadata = $dm->getClassMetadata(get_class($obj));
        $uow->recomputeSingleDocumentChangeSet($metadata, $obj);
    }

    /**
     * {@inheritDoc}
     */
    public function getReflectionClass($obj)
    {
        if ($obj instanceof Proxy) {
            return new \ReflectionClass(get_parent_class($obj));
        }

        return new \ReflectionClass($obj);
    }
}
