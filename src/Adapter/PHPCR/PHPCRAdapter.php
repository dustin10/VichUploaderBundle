<?php

namespace Vich\UploaderBundle\Adapter\PHPCR;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ODM\PHPCR\Event\PreUpdateEventArgs;
use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * @author Ben Glassman <bglassman@gmail.com>
 * @final
 *
 * @internal
 */
class PHPCRAdapter implements AdapterInterface
{
    public function recomputeChangeSet(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        $objectManager = $event->getObjectManager();
        $uow = $objectManager->getUnitOfWork();
        $uow->computeSingleDocumentChangeSet($object);
    }

    /**
     * @param PreUpdateEventArgs $event
     */
    public function getChangeSet(LifecycleEventArgs $event): array
    {
        return $event->getDocumentChangeSet();
    }
}
