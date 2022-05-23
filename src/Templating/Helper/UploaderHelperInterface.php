<?php

namespace Vich\UploaderBundle\Templating\Helper;

interface UploaderHelperInterface
{
    /**
     * Gets the public path for the file associated with the object.
     *
     * @param object      $obj       The object
     * @param string|null $fieldName The field name
     *
     * @return string|null The public asset path or null if file not stored
     */
    public function asset($obj, ?string $fieldName = null);
}
