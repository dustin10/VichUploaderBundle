<?php

namespace Vich\UploaderBundle\Naming;

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
     * @param object $obj The object the upload is attached to.
     * @param string $field The name of the uploadable field to generate a name for.
     * @return string The file name.
     */
    function name($obj, $field);
}
