<?php

namespace Vich\UploaderBundle\Adapter\PHPCR;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Adapter\Doctrine\DoctrineAdapter;

/**
 * PHPCRAdapter.
 *
 * @author Ben Glassman <bglassman@gmail.com>
 */
class PHPCRAdapter extends DoctrineAdapter implements AdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getObjectFromArgs($event)
    {
        return $event->getEntity();
    }

    /**
     * {@inheritDoc}
     */
    public function recomputeChangeSet($event)
    {
        $object = $this->getObjectFromArgs($event);

        $dm = $event->getObjectManager();
        $uow = $dm->getUnitOfWork();
        $uow->computeSingleDocumentChangeSet($object);
    }

}
