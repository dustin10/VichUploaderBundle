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
     * Populates all UploadableField-properties in the object with
     * a Symfony\Component\HttpFoundation\File\File object representing
     * their file. The paths of these files are derived from the
     * corresponding filename properties.
     *
     * @param object $obj The object.
     */
    function populateUploadableFields($obj);

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
