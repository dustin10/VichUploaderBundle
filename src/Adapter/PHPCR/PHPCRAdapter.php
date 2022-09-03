<?php

namespace Vich\UploaderBundle\Adapter\PHPCR;

use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * @author Ben Glassman <bglassman@gmail.com>
 *
 * @internal
 */
final class PHPCRAdapter implements AdapterInterface
{
    public function getObjectFromArgs(object $event): object
    {
        return $event->getObject();
    }

    public function recomputeChangeSet(object $event): void
    {
        $object = $this->getObjectFromArgs($event);

        $dm = $event->getObjectManager();
        $uow = $dm->getUnitOfWork();
        $uow->computeSingleDocumentChangeSet($object);
    }
}
