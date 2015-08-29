<?php

namespace Vich\UploaderBundle\Exception;

class MappingNotFoundException extends \RuntimeException
{
    public static function createNotFoundForClassAndField($mapping, $class, $field)
    {
        return new MappingNotFoundException(
            sprintf('Mapping "%s" does not exist. The configuration for the class "%s" is probably incorrect as the mapping to use for the field "%s" could not be found.', $mapping, $class, $field)
        );
    }
}
