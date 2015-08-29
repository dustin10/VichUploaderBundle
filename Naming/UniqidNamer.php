<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * UniqidNamer
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class UniqidNamer implements NamerInterface
{
    use Polyfill\FileExtensionTrait;

    /**
     * {@inheritDoc}
     */
    public function name($object, PropertyMapping $mapping)
    {
        $file = $mapping->getFile($object);
        $name = uniqid();

        if ($extension = $this->getExtension($file)) {
            $name = sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }
}
