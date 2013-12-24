<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * DirectoryNamerInterface.
 *
 * @author Kevin bond <kevinbond@gmail.com>
 */
interface DirectoryNamerInterface
{
    /**
     * Creates a directory name for the file being uploaded.
     *
     * @param Propertymapping $mapping The mapping to use to manipulate the given object.
     * @param object          $object  The object the upload is attached to.
     *
     * @return string The directory name.
     */
    public function name(PropertyMapping $mapping, $object);
}
