<?php

namespace Vich\UploaderBundle\Naming;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * OrignameNamer
 *
 * @author Ivan Borzenkov <ivan.borzenkov@gmail.com>
 */
class OrignameNamer implements NamerInterface
{
    /**
     * {@inheritDoc}
     */
    public function name($obj, $field)
    {
        $refObj = new \ReflectionObject($obj);

        $refProp = $refObj->getProperty($field);
        $refProp->setAccessible(true);

        $file = $refProp->getValue($obj);

        /** @var $file UploadedFile */

        return uniqid().'_'.$file->getClientOriginalName();
    }
} 
