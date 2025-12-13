<?php

namespace Vich\UploaderBundle\Injector;

use Vich\UploaderBundle\Mapping\PropertyMappingInterface;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface FileInjectorInterface
{
    /**
     * Injects the uploadable field of the specified object and mapping.
     *
     * The field is populated with a \Symfony\Component\HttpFoundation\File\File instance.
     *
     * @param array|object             $obj     The object
     * @param PropertyMappingInterface $mapping The mapping representing the field to populate
     */
    public function injectFile(array|object $obj, PropertyMappingInterface $mapping): void;
}
