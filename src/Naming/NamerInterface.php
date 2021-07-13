<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * NamerInterface.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 * @final
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
     */
    public function name($object, PropertyMapping $mapping): string;
}
