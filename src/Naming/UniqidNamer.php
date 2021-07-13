<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * UniqidNamer.
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 * @final
 */
class UniqidNamer implements NamerInterface
{
    use Polyfill\FileExtensionTrait;

    public function name($object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);
        $name = \str_replace('.', '', \uniqid('', true));
        $extension = $this->getExtension($file);

        if (\is_string($extension) && '' !== $extension) {
            $name = \sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }
}
