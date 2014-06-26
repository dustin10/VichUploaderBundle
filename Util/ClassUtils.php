<?php

namespace Vich\UploaderBundle\Util;

class ClassUtils
{
    /**
     * Gets class name for the object, taking doctrine proxies into account.
     *
     * @param object $object The object.
     *
     * @return string The FQCN of the given object.
     */
    public static function getClass($object)
    {
        if (class_exists('\Doctrine\Common\Util\ClassUtils')) {
            return \Doctrine\Common\Util\ClassUtils::getClass($object);
        }

        return get_class($object);
    }
}
