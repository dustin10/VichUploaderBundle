<?php

namespace Vich\UploaderBundle\Adapter;

use Doctrine\Common\EventArgs;

interface DoctrineAdapter extends AdapterInterface
{
    /**
     * @param EventArgs $event
     *
     * @return array[]
     */
    public function getIdentityMapForEvent(EventArgs $event);
}
