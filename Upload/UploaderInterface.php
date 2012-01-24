<?php

namespace Vich\UploaderBundle\Upload;

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
     * @param object $obj The object.
     */
    function upload($obj);
    
    /**
     * Removes the file associated with the object if configured to
     * do so.
     * 
     * @param object $obj The object.
     */
    function remove($obj);
    
    /**
     * Gets the path relative to the web root directory  for the 
     * object.
     * 
     * @param object $obj The object.
     * @param string $field The field.
     * @return string The path.
     */
    function getPublicPath($obj, $field);
}
