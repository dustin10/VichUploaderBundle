<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Model\UploadableInterface;

/**
 * NamerInterface.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface NamerInterface
{
    /**
     * Creates a name for the file upload.
     * 
     * @param UploadableInterface $uploadable The object the upload is attached to.
     * @return string The file name.
     */
    function name(UploadableInterface $uploadable);
}
