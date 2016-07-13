<?php
namespace Vich\UploaderBundle\EventListener\PropelGe;

use Vich\UploaderBundle\EventListener\Propel\BaseListener as PropelBaseListener;
use Vich\UploaderBundle\Util\ClassUtils;

/**
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
abstract class BaseListener extends PropelBaseListener
{
    /**
     * Checks if the given object is uploadable using the current mapping.
     *
     * @param mixed $object The object to test.
     *
     * @return bool
     */
    protected function isUploadable($object)
    {
        return $this->metadata->isUploadable(ClassUtils::getClass($object), $this->mapping);
    }
}
