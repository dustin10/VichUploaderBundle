<?php

namespace Vich\UploaderBundle\Injector;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * FileInjectorInterface.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface FileInjectorInterface
{
    /**
     * Injects the uploadable fields of the specified object
     * with a populated Symfony\Component\HttpFoundation\File\File
     * instance if the field is configured for injection.
     *
     * @param object          $object  The object.
     * @param PropertyMapping $mapping The mapping representing the field to populate.
     */
    public function injectFile($object, PropertyMapping $mapping);
}
