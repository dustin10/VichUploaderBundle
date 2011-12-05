<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Model\UploadableInterface;

/**
 * OriginalFilenameNamer.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class OriginalFilenameNamer implements NamerInterface
{
    /**
     * {@inheritDoc}
     */
    public function name(UploadableInterface $uploadable)
    {
        return $uploadable->getFile()->getClientOriginalName();
    }
}
