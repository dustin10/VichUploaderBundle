<?php

namespace Vich\UploaderBundle\Adapter\ORM;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Doctrine\Common\Persistence\Proxy;

/**
 * DoctrineORMAdapter.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class DoctrineORMAdapter implements AdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getObjectFromEvent($event)
    {
        /* @var $event \Doctrine\Common\EventArgs */

        return $event->getEntity();
    }

    /**
     * {@inheritDoc}
     */
    public function recomputeChangeSet($event)
    {
        /* @var $event \Doctrine\Common\EventArgs */
        $object = $this->getObjectFromEvent($event);

        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata(get_class($object));
        $uow->recomputeSingleEntityChangeSet($metadata, $object);
    }

    /**
     * {@inheritDoc}
     */
    public function getReflectionClass($object)
    {
        if ($object instanceof Proxy) {
            return new \ReflectionClass(get_parent_class($object));
        }

        return new \ReflectionClass($obj);
    }
}
