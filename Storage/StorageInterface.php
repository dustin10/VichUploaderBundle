<?php

namespace Vich\UploaderBundle\Storage;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * StorageInterface.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface StorageInterface
{
    /**
     * Uploads the file in the uploadable field of the specified object
     * according to the property configuration.
     *
     * @param object          $obj     The object.
     * @param PropertyMapping $mapping The mapping representing the field to upload.
     */
    public function upload($obj, PropertyMapping $mapping);

    /**
     * Removes the files associated with the given mapping.
     *
     * @param object          $obj     The object.
     * @param PropertyMapping $mapping The mapping representing the field to remove.
     */
    public function remove($obj, PropertyMapping $mapping);

    /**
     * Resolves the path for a file based on the specified object
     * and mapping name.
     *
     * @param object $obj       The object.
     * @param string $fieldName The field to use.
     * @param string $className The object's class. Mandatory if $obj can't be used to determine it.
     * @param bool   $relative  Whether the path should be relative or absolute.
     *
     * @return string The path.
     */
    public function resolvePath($obj, $fieldName, $className = null, $relative = false);

    /**
     * Resolves the uri based on the specified object and mapping name.
     *
     * @param object $obj       The object.
     * @param string $fieldName The field to use.
     * @param string $className The object's class. Mandatory if $obj can't be used to determine it.
     *
     * @return string The uri.
     */
    public function resolveUri($obj, $fieldName, $className = null);

    /**
     * Returns a read-only stream based on the specified object and mapping
     * name.
     *
     * @param object $obj       The object.
     * @param string $fieldName The field to use.
     * @param string $className The object's class. Mandatory if $obj can't be used to determine it.
     *
     * @return string The uri.
     */
    public function resolveStream($obj, $fieldName, $className = null);
}
