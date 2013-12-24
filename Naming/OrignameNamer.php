<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

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
    public function name(PropertyMapping $mapping, $object)
    {
        $file = $mapping->getFile($object);

        /** @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */

        return uniqid().'_'.$file->getClientOriginalName();
    }
}
