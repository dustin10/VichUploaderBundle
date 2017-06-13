<?php

namespace Vich\UploaderBundle\Adapter\ODM\MongoDB;

use Doctrine\Common\EventArgs;
use Doctrine\ODM\MongoDB\Event\PreFlushEventArgs;
use Vich\UploaderBundle\Adapter\DoctrineAdapter;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class MongoDBAdapter implements DoctrineAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getObjectFromArgs($event)
    {
        return $event->getDocument();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityMapForEvent(EventArgs $event)
    {
        /* @var $event PreFlushEventArgs */
        $em = $event->getDocumentManager();

        return $em->getUnitOfWork()->getIdentityMap();
    }
}
