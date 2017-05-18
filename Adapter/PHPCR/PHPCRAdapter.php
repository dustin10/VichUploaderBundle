<?php

namespace Vich\UploaderBundle\Adapter\PHPCR;

use Doctrine\Common\EventArgs;
use Doctrine\Common\Persistence\Event\ManagerEventArgs;
use Vich\UploaderBundle\Adapter\DoctrineAdapter;

/**
 * @author Ben Glassman <bglassman@gmail.com>
 */
class PHPCRAdapter implements DoctrineAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getObjectFromArgs($event)
    {
        return $event->getEntity();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityMapForEvent(EventArgs $event)
    {
        /* @var $event ManagerEventArgs */
        $em = $event->getObjectManager();
        //FIXME: fix getIdentityMap call https://github.com/doctrine/phpcr-odm/blob/master/lib/Doctrine/ODM/PHPCR/UnitOfWork.php

        return $em->getUnitOfWork()->getIdentityMap();
    }
}
