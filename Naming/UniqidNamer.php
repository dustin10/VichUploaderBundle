<?php

namespace Vich\UploaderBundle\Naming;

use Symfony\Component\HttpFoundation\File\File;

/**
 * UniqidNamer
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class UniqidNamer implements NamerInterface
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
        $name = uniqid();

        if ($extension = $this->getExtension($file)) {
            $name = sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }

    protected function getExtension(File $file)
    {
        if ($extension = $file->getExtension()) {
            return $extension;
        }

        if ($extension = $file->guessExtension()) {
            return $extension;
        }

        return null;
    }
}
