<?php

namespace Vich\UploaderBundle\Adapter\ORM;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Vich\UploaderBundle\Adapter\DoctrineAdapter;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class DoctrineORMAdapter implements DoctrineAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getObjectFromArgs($event)
    {
        return $event->getEntity();
    }

    public function getIdentityMapForEvent(EventArgs $event)
    {
        /* @var $event PreFlushEventArgs */
        $em = $event->getEntityManager();

        return $em->getUnitOfWork()->getIdentityMap();
    }
}
