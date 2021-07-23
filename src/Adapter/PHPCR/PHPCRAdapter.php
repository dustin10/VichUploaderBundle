<?php

namespace Vich\UploaderBundle\Adapter\PHPCR;

use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * @author Ben Glassman <bglassman@gmail.com>
 * @final
 *
 * @internal
 */
class PHPCRAdapter implements AdapterInterface
{
    public function getObjectFromArgs($event)
    {
        return $event->getObject();
    }

    public function recomputeChangeSet($event): void
    {
        $object = $this->getObjectFromArgs($event);

        $dm = $event->getObjectManager();
        $uow = $dm->getUnitOfWork();
        $uow->computeSingleDocumentChangeSet($object);
    }
}
