<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * NamerInterface.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface NamerInterface
{
    /**
     * Creates a name for the file being uploaded.
     *
     * @param Propertymapping $mapping The mapping to use to manipulate the given object.
     * @param object          $object  The object the upload is attached to.
     *
     * @return string The file name.
     */
    public function name(PropertyMapping $mapping, $object);
}
