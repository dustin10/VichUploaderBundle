<?php

namespace Vich\UploaderBundle\EventListener;

use Doctrine\Common\EventArgs;

class InjectListener extends AbstractListener
{
    /**
     * Populates uploadable fields from filename properties
     * if necessary.
     *
     * @param \Doctrine\Common\EventArgs $args
     */
    public function postLoad(EventArgs $args)
    {
        $obj = $this->adapter->getObjectFromArgs($args);
        if ($this->isUploadable($obj)) {
            $this->injector->injectFiles($obj);
        }
    }}