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
     * @param  object $obj   The object.
     * @param  string $field The field.
     * @return string The path.
     */
    public function resolvePath($obj, $field);

    /**
     * Resolves the uri for any based on the specified object
     * and field name.
     *
     * @param  object $obj   The object.
     * @param  string $field The field.
     * @return string The uri.
     */
    public function resolveUri($obj, $field);
}
