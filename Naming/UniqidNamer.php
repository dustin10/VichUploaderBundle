<?php

namespace Vich\UploaderBundle\Naming;

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

        if ($extension = $file->guessExtension()) {
            $name = sprintf('%s.%s', $name, $file->guessExtension());
        }

        return $name;
    }
}
