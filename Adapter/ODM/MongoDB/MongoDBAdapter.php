<?php

namespace Vich\UploaderBundle\Adapter\ODM\MongoDB;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Doctrine\Common\EventArgs;

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
}