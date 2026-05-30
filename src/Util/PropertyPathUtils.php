<?php

namespace Vich\UploaderBundle\Util;

final class PropertyPathUtils
{
    private function __construct()
    {
    }

    /**
     * Fixes a given propertyPath to make it usable both with arrays and
     * objects.
     * Ie: if the given object is in fact an array, the property path must
     * look like [myPath].
     *
     * @param object|array $object       The object to inspect
     * @param string       $propertyPath The property path to fix
     *
     * @return string The fixed property path
     */
    public static function fixPropertyPath(object|array $object, string $propertyPath): string
    {
        if (!\is_array($object)) {
            return $propertyPath;
        }

        return '[' === $propertyPath[0] ? $propertyPath : \sprintf('[%s]', $propertyPath);
    }
}
