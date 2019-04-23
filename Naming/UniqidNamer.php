<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * UniqidNamer.
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class UniqidNamer implements NamerInterface
{
    use Polyfill\FileExtensionTrait;

    public function name($object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);
        $name = \str_replace('.', '', \uniqid('', true));

        if ($extension = $this->getExtension($file)) {
            $name = \sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }
}
