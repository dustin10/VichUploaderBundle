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
    public function upload($obj);

    /**
     * Removes the files associated with the object if configured to
     * do so.
     *
     * @param object $obj The object.
     */
    public function remove($obj);

    /**
     * Resolves the path for a file based on the specified object
     * and field name.
     *
     * @param object $obj       The object.
     * @param string $field     The field.
     * @param string $className The object's class. Mandatory if $obj can't be used to determine it.
     *
     * @return string The path.
     */
    public function resolvePath($obj, $field, $className = null);

    /**
     * Resolves the uri for any based on the specified object
     * and field name.
     *
     * @param object $obj       The object.
     * @param string $field     The field.
     * @param string $className The object's class. Mandatory if $obj can't be used to determine it.
     *
     * @return string The uri.
     */
    public function resolveUri($obj, $field, $className = null);
}
