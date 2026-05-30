<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 *
 * @phpstan-template T of object|array
 */
interface NamerInterface
{
    /**
     * Creates a name for the file being uploaded.
     *
     * @param object|array    $object  The object or array the upload is attached to
     * @param PropertyMapping $mapping The mapping to use to manipulate the given object
     *
     * @phpstan-param T $object
     *
     * @return string The file name
     */
    public function name(object|array $object, PropertyMapping $mapping): string;
}
