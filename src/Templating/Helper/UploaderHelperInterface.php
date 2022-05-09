<?php

namespace Vich\UploaderBundle\Templating\Helper;

interface UploaderHelperInterface
{
    /**
     * Gets the public path for the file associated with the
     * object.
     *
     * @param object|array $obj       The object
     * @param string|null  $fieldName The field name
     * @param string|null  $className The object's class. Mandatory if $obj can't be used to determine it
     *
     * @return string|null The public asset path or null if file not stored
     */
    public function asset($obj, ?string $fieldName = null, ?string $className = null);
}
