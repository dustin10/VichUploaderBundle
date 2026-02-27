<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Vich\UploaderBundle\Mapping\AttributeInterface;

/**
 * @internal
 */
interface AttributeReaderInterface
{
    /** @return AttributeInterface[] */
    public function getClassAttributes(\ReflectionClass $class): array;

    public function getClassAttribute(\ReflectionClass $class, string $attributeName): ?AttributeInterface;

    /** @return AttributeInterface[] */
    public function getMethodAttributes(\ReflectionMethod $method): array;

    public function getMethodAttribute(\ReflectionMethod $method, string $attributeName): ?AttributeInterface;

    /** @return AttributeInterface[] */
    public function getPropertyAttributes(\ReflectionProperty $property): array;

    public function getPropertyAttribute(\ReflectionProperty $property, string $attributeName): ?AttributeInterface;
}
