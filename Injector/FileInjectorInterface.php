<?php

namespace Vich\UploaderBundle\Injector;

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
     * @param object $obj The object.
     */
    public function injectFiles($obj);
}
