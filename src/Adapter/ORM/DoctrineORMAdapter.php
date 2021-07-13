<?php

namespace Vich\UploaderBundle\Adapter\ORM;

use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * DoctrineORMAdapter.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 * @final
 *
 * @internal
 */
class DoctrineORMAdapter implements AdapterInterface
{
    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $event
     */
    public function getObjectFromArgs($event)
    {
        return $event->getEntity();
    }

    /**
     * @param \Doctrine\ORM\Event\PreUpdateEventArgs $event
     */
    public function recomputeChangeSet($event): void
    {
        $object = $this->getObjectFromArgs($event);

        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata(\get_class($object));
        $uow->recomputeSingleEntityChangeSet($metadata, $object);
    }
}
