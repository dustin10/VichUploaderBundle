<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Vich\UploaderBundle\Mapping\AnnotationInterface;

/**
 * @internal
 */
final class AttributeReader
{
    /** @return AnnotationInterface[] */
    public function getClassAnnotations(ReflectionClass $class): array
    {
        return $this->convertToAttributeInstances($class->getAttributes());
    }

    public function getClassAnnotation(ReflectionClass $class, string $annotationName): ?AnnotationInterface
    {
        return $this->getClassAnnotations($class)[$annotationName] ?? null;
    }

    /** @return AnnotationInterface[] */
    public function getMethodAnnotations(ReflectionMethod $method): array
    {
        return $this->convertToAttributeInstances($method->getAttributes());
    }

    public function getMethodAnnotation(ReflectionMethod $method, string $annotationName): ?AnnotationInterface
    {
        return $this->getMethodAnnotations($method)[$annotationName] ?? null;
    }

    /** @return AnnotationInterface[] */
    public function getPropertyAnnotations(ReflectionProperty $property): array
    {
        return $this->convertToAttributeInstances($property->getAttributes());
    }

    public function getPropertyAnnotation(ReflectionProperty $property, string $annotationName): ?AnnotationInterface
    {
        return $this->getPropertyAnnotations($property)[$annotationName] ?? null;
    }

    /**
     * @param ReflectionAttribute[] $attributes
     *
     * @return AnnotationInterface[]
     */
    private function convertToAttributeInstances(array $attributes): array
    {
        $instances = [];

        foreach ($attributes as $attribute) {
            $attributeName = $attribute->getName();
            $instance = $attribute->newInstance();

            if (!$instance instanceof AnnotationInterface) {
                continue;
            }

            $instances[$attributeName] = $instance;
        }

        return $instances;
    }
}
