<?php

namespace Vich\UploaderBundle\Storage;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface StorageInterface
{
    /**
     * Uploads the file in the uploadable field of the specified object
     * according to the property configuration.
     *
     * @param object          $obj     The object
     * @param PropertyMapping $mapping The mapping representing the field to upload
     */
    public function upload($obj, PropertyMapping $mapping): void;

    /**
     * Removes the files associated with the given mapping.
     *
     * @param object          $obj     The object
     * @param PropertyMapping $mapping The mapping representing the field to remove
     */
    public function remove($obj, PropertyMapping $mapping, ?string $forcedFilename = null): ?bool;

    /**
     * Resolves the path for a file based on the specified object
     * and mapping name.
     *
     * @param object|array $obj       The object
     * @param string|null  $fieldName The field to use
     * @param string|null  $className The object's class. Mandatory if $obj can't be used to determine it
     * @param bool         $relative  Whether the path should be relative or absolute
     *
     * @return string The path
     */
    public function resolvePath($obj, ?string $fieldName = null, ?string $className = null, ?bool $relative = false): ?string;

    //TODO: inconsistency - use PropertyMapping instead of fieldName+className

    /**
     * Resolves the uri based on the specified object and mapping name.
     *
     * @param object|array $obj       The object
     * @param string|null  $fieldName The field to use
     * @param string|null  $className The object's class. Mandatory if $obj can't be used to determine it
     *
     * @return string|null The uri or null if file not stored
     */
    public function resolveUri($obj, ?string $fieldName = null, ?string $className = null): ?string;

    /**
     * Returns a read-only stream based on the specified object and mapping name.
     *
     * @param object|array $obj       The object
     * @param string       $fieldName The field to use
     * @param string       $className The object's class. Mandatory if $obj can't be used to determine it
     *
     * @return resource|null The resolved resource or null if file not stored
     */
    public function resolveStream($obj, string $fieldName, ?string $className = null);
}
