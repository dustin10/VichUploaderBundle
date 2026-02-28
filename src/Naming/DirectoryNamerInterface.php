<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMappingInterface;

/**
 * NamerInterface.
 *
 * @author Kevin bond <kevinbond@gmail.com>
 *
 * @phpstan-template T of object|array
 */
interface DirectoryNamerInterface
{
    /**
     * Creates a directory name for the file being uploaded.
     *
     * @param object|array             $object  The object or array the upload is attached to
     * @param PropertyMappingInterface $mapping The mapping to use to manipulate the given object
     *
     * @phpstan-param T $object
     *
     * @return string The directory name
     */
    public function directoryName(object|array $object, PropertyMappingInterface $mapping): string;
}
