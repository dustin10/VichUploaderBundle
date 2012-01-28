<?php

namespace Vich\UploaderBundle\Storage;

/**
 * StorageInterface.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface StorageInterface
{
    /**
     * Uploads the files in the uploadable fields of the
     * specified object according to the property configuration.
     *
     * @param object $obj The object.
     */
    function upload($obj);

    /**
     * Removes the files associated with the object if configured to
     * do so.
     *
     * @param object $obj The object.
     */
    function remove($obj);

    /**
     * Resolves the path for a file based on the specified object
     * and field name.
     *
     * @param object $obj The object.
     * @param string $field The field.
     * @return string The path.
     */
    function resolvePath($obj, $field);
}
