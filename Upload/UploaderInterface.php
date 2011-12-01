<?php

namespace Vich\UploaderBundle\Upload;

use Vich\UploaderBundle\Model\UploadableInterface;

/**
 * UploaderInterface.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface UploaderInterface
{
    /**
     * Uploads the uploadable.
     * 
     * @param UploadableInterface $uploadable The uploadable object.
     */
    function upload(UploadableInterface $uploadable);
    
    /**
     * Removes the file associated with the uploadable if configured to
     * do so.
     * 
     * @param UploadableInterface $uploadable The uploadable object.
     */
    function remove(UploadableInterface $uploadable);
}
