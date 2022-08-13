<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * NamerInterface.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 *
 * @phpstan-template T of object
 */
interface NamerInterface
{
    /**
     * Creates a name for the file being uploaded.
     *
     * @param object          $object  The object the upload is attached to
     * @param PropertyMapping $mapping The mapping to use to manipulate the given object
     *
     * @return string The file name
     *
     * @phpstan-param T $object
     */
    public function name(object $object, PropertyMapping $mapping): string;
}
