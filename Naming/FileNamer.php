<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Model\UploadableInterface;

/**
 * FileNamer.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class FileNamer implements NamerInterface
{
    /**
     * {@inheritDoc}
     */
    public function name(UploadableInterface $uploadable)
    {
        return $uploadable->getFile()->getClientOriginalName();
    }
}
