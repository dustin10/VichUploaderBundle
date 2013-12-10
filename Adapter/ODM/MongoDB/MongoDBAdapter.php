<?php

namespace Vich\UploaderBundle\Adapter\ODM\MongoDB;

use Vich\UploaderBundle\Adapter\AdapterInterface;
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
    public function getObjectFromEvent($event)
    {
        /* @var $event \Doctrine\Common\EventArgs */

        return $event->getDocument();
    }

    /**
     * {@inheritDoc}
     */
    public function recomputeChangeSet($event)
    {
        /* @var $event \Doctrine\Common\EventArgs */
        $object = $this->getObjectFromEvent($event);

        $dm = $event->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        $metadata = $dm->getClassMetadata(get_class($object));
        $uow->recomputeSingleDocumentChangeSet($metadata, $object);
    }

    /**
     * {@inheritDoc}
     */
    public function getReflectionClass($object)
    {
        if ($object instanceof Proxy) {
            return new \ReflectionClass(get_parent_class($object));
        }

        return new \ReflectionClass($object);
    }
}
