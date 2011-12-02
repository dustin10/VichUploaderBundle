<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Model\UploadableInterface;

/**
 * MD5Namer.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class MD5Namer implements NamerInterface
{
    /**
     * {@inheritDoc}
     */
    public function name(UploadableInterface $uploadable)
    {
        $file = $uploadable->getFile();
        
        $extension = $file->guessExtension();
        $name = md5($file->getClientOriginalName() . time());
        
        return sprintf('%s.%s', $name, $extension);
    }
}
