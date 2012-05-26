<?php

namespace Vich\UploaderBundle\Naming;

/**
 * Names file according to the database record's ID
 *
 * @author David de Boer <david@ddeboer.nl>
 */
class GeneratedIdNamer implements NamerInterface
{
    /**
     * {@inheritdoc}
     */
    public function name($obj, $property)
    {
        $file = $property->getValue($obj);

        return $obj->getId() . '.' . $file->guessExtension();
    }
}