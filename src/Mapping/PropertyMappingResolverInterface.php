<?php

declare(strict_types=1);

namespace Vich\UploaderBundle\Mapping;

use Vich\UploaderBundle\Exception\MappingNotFoundException;

interface PropertyMappingResolverInterface
{
    /**
     * Creates the property mapping from the read annotation and configured mapping.
     *
     * @param object|array $obj         The object
     * @param string       $fieldName   The field name
     * @param array        $mappingData The mapping data
     *
     * @return PropertyMapping The property mapping
     *
     * @throws \LogicException
     * @throws MappingNotFoundException
     */
    public function resolve(object|array $obj, string $fieldName, array $mappingData): PropertyMapping;
}
