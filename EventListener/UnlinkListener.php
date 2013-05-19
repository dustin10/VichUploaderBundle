<?php

namespace Vich\UploaderBundle\EventListener;

use Doctrine\Common\EventArgs;

class UnlinkListener extends AbstractListener
{
    /**
     * Removes the file if necessary.
     *
     * @param EventArgs $args The event arguments.
     */    
    public function postRemove(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);
        if ($this->isUploadable($obj)) {
            $this->storage->remove($obj);
        }
    }
}