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
    
    /**
     * Gets the path relative to the web root directory  for the 
     * uploadable object.
     * 
     * @param UploadableInterface $uploadable The uploadable object.
     */
    function getPublicPath(UploadableInterface $uploadable);
}
