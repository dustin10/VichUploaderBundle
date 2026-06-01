<?php

namespace Vich\TestBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

final class DummyNamer implements NamerInterface
{
    public function name(object $object, PropertyMapping $mapping): string
    {
        /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
        $file = $mapping->getFile($object);

        return $file->getClientOriginalName();
    }
}
