<?php

namespace Vich\UploaderBundle\Util;

/**
 * @internal
 */
final class ClassUtils
{
    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Gets class name for the object, taking doctrine proxies into account.
     *
     * @param object $object The object
     *
     * @return string The FQCN of the given object
     */
    public static function getClass(object $object): string
    {
        $className = $object::class;
        $positionPm = 0;

        // see original code @ https://github.com/api-platform/core/blob/6e9ccf7418bf973d273b125d55ccc521b89afb06/src/Util/ClassInfoTrait.php#L38
        // __CG__: Doctrine Common Marker for Proxy (ODM < 2.0 and ORM < 3.0)
        // __PM__: Ocramius Proxy Manager (ODM >= 2.0)
        if ((false === $positionCg = \strrpos($className, '\\__CG__\\')) &&
            (false === $positionPm = \strrpos($className, '\\__PM__\\'))) {
            return $className;
        }

        if (false !== $positionCg) {
            return \substr($className, $positionCg + 8);
        }

        $className = \ltrim($className, '\\');

        return \substr(
            $className,
            8 + $positionPm,
            \strrpos($className, '\\') - ($positionPm + 8)
        );
    }
}
