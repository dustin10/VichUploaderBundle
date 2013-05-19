<?php

namespace Vich\UploaderBundle\EventListener;

use Doctrine\Common\EventArgs;

class UploadListener extends AbstractListener
{
    /**
     * Checks for for file to upload.
     *
     * @param \Doctrine\Common\EventArgs $args The event arguments.
     */
    public function prePersist(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);
        if ($this->isUploadable($obj)) {
            $this->storage->upload($obj);
        }
    }

    /**
     * Update the file and file name if necessary.
     *
     * @param EventArgs $args The event arguments.
     */
    public function preUpdate(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);
        if ($this->isUploadable($obj)) {
            $this->storage->upload($obj);

            $this->adapter->recomputeChangeSet($args);
        }
    }
}
