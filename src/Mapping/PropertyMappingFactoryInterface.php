<?php

namespace Vich\UploaderBundle\Mapping;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface PropertyMappingFactoryInterface
{
    /**
     * @return array|PropertyMappingInterface[]
     */
    public function fromObject(object|array $obj, ?string $className = null, ?string $mappingName = null): array;

    public function fromField(object|array $obj, string $field, ?string $className = null): ?PropertyMappingInterface;

    public function fromFirstField(object|array $obj, ?string $className = null): ?PropertyMappingInterface;
}
