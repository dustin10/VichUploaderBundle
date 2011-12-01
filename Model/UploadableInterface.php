<?php

namespace Vich\UploaderBundle\Model;

/**
 * UploadableInterface.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface UploadableInterface
{
    /**
     * Returns the uploaded file.
     * 
     * @return UploadedFile The uploaded file.
     */
    function getFile();
    
    /**
     * Gets the file name.
     * 
     * @return string The file name.
     */
    function getFileName();
    
    /**
     * Sets the file name.
     * 
     * @param string $fileName The file name.
     */
    function setFileName($fileName);
}