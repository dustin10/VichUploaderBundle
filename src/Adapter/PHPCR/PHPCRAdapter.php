<?php

namespace Vich\UploaderBundle\Adapter\PHPCR;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\ManagerEventArgs;
use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * @author Ben Glassman <bglassman@gmail.com>
 *
 * @internal
 */
final readonly class PHPCRAdapter implements AdapterInterface
{
    public function recomputeChangeSet(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        $objectManager = $event->getObjectManager();
        $uow = $objectManager->getUnitOfWork();
        $uow->computeSingleDocumentChangeSet($object);
    }

    public function getObjectsToFlush(ManagerEventArgs $event, bool $includeInsertions): iterable
    {
        return [];
    }
}
