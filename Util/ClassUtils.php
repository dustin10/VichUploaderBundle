<?php

namespace Vich\UploaderBundle\Util;

use Doctrine\Common\Util\ClassUtils as DoctrineClassUtils;

class ClassUtils
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
    public static function getClass($object)
    {
        if (class_exists(DoctrineClassUtils::class)) {
            return DoctrineClassUtils::getClass($object);
        }

        return get_class($object);
    }
}
