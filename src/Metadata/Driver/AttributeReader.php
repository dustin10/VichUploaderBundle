<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Vich\UploaderBundle\Mapping\AttributeInterface;

/**
 * @internal
 */
final class AttributeReader
{
    /** @return AttributeInterface[] */
    public function getClassAttributes(\ReflectionClass $class): array
    {
        return $this->convertToAttributeInstances($class->getAttributes());
    }

    public function getClassAttribute(\ReflectionClass $class, string $attributeName): ?AttributeInterface
    {
        return $this->getClassAttributes($class)[$attributeName] ?? null;
    }

    /** @return AttributeInterface[] */
    public function getMethodAttributes(\ReflectionMethod $method): array
    {
        return $this->convertToAttributeInstances($method->getAttributes());
    }

    public function getMethodAttribute(\ReflectionMethod $method, string $attributeName): ?AttributeInterface
    {
        return $this->getMethodAttributes($method)[$attributeName] ?? null;
    }

    /** @return AttributeInterface[] */
    public function getPropertyAttributes(\ReflectionProperty $property): array
    {
        return $this->convertToAttributeInstances($property->getAttributes());
    }

    public function getPropertyAttribute(\ReflectionProperty $property, string $attributeName): ?AttributeInterface
    {
        return $this->getPropertyAttributes($property)[$attributeName] ?? null;
    }

    /**
     * @param \ReflectionAttribute[] $attributes
     *
     * @return AttributeInterface[]
     */
    private function convertToAttributeInstances(array $attributes): array
    {
        $instances = [];

        foreach ($attributes as $attribute) {
            $attributeName = $attribute->getName();
            $instance = $attribute->newInstance();

            if (!$instance instanceof AttributeInterface) {
                continue;
            }

            $instances[$attributeName] = $instance;
        }

        return $instances;
    }
}
