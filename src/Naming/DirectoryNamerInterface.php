<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * NamerInterface.
 *
 * @author Kevin bond <kevinbond@gmail.com>
 *
 * @phpstan-template T of object
 */
interface DirectoryNamerInterface
{
    /**
     * Creates a directory name for the file being uploaded.
     *
     * @param object|array    $object  The object or array the upload is attached to
     * @param PropertyMapping $mapping The mapping to use to manipulate the given object
     *
     * @return string The directory name
     *
     * @phpstan-param T $object
     */
    public function directoryName(object|array $object, PropertyMapping $mapping): string;
}
