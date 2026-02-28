<?php

namespace Vich\TestBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Naming\NamerInterface;

final class DummyNamer implements NamerInterface
{
    public function name(object|array $object, PropertyMappingInterface $mapping): string
    {
        /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
        $file = $mapping->getFile($object);

        return $file->getClientOriginalName();
    }
}
