<?php

declare(strict_types=1);

namespace Vich\UploaderBundle\Mapping;

interface PropertyMappingFactory
{
    public function fromObject(object|array $obj, ?string $className = null, ?string $mappingName = null): array;

    public function fromField(object|array $obj, string $field, ?string $className = null): ?PropertyMapping;

    public function fromFirstField(object|array $obj, ?string $className = null): ?PropertyMapping;
}
