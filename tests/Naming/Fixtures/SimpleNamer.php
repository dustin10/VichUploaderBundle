<?php

namespace Vich\UploaderBundle\Tests\Naming\Fixtures;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

/**
 * Simple namer that doesn't implement ConfigurableInterface.
 * This simulates what most custom namers in user applications look like.
 */
class SimpleNamer implements NamerInterface
{
    use \Vich\UploaderBundle\Naming\Polyfill\FileExtensionTrait;

    public function name(object|array $object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);
        $originalName = $file->getClientOriginalName();
        $basename = \pathinfo($originalName, \PATHINFO_FILENAME);
        $extension = $this->getExtension($file);

        return 'simple_'.$basename.($extension ? '.'.$extension : '');
    }
}
