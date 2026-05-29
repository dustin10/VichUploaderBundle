<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Vich\UploaderBundle\Mapping\AttributeInterface;

/**
 * @internal
 */
final readonly class AttributeReader
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

    /** @return AttributeInterface[] */
    public function getClassAnnotations(\ReflectionClass $class): array
    {
        return $this->getClassAttributes($class);
    }

    public function getClassAnnotation(\ReflectionClass $class, string $annotationName): ?AttributeInterface
    {
        return $this->getClassAttribute($class, $annotationName);
    }

    /** @return AttributeInterface[] */
    public function getMethodAnnotations(\ReflectionMethod $method): array
    {
        return $this->getMethodAttributes($method);
    }

    public function getMethodAnnotation(\ReflectionMethod $method, string $annotationName): ?AttributeInterface
    {
        return $this->getMethodAttribute($method, $annotationName);
    }

    /** @return AttributeInterface[] */
    public function getPropertyAnnotations(\ReflectionProperty $property): array
    {
        return $this->getPropertyAttributes($property);
    }

    public function getPropertyAnnotation(\ReflectionProperty $property, string $annotationName): ?AttributeInterface
    {
        return $this->getPropertyAttribute($property, $annotationName);
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
