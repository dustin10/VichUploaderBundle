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
     * @deprecated since 2.9, use getClassAttributes() instead
     *
     * @return AttributeInterface[]
     */
    public function getClassAnnotations(\ReflectionClass $class): array
    {
        trigger_deprecation('vich/uploader-bundle', '2.9', 'Method "%s" is deprecated, use "getClassAttributes()" instead.', __METHOD__);

        return $this->getClassAttributes($class);
    }

    /**
     * @deprecated since 2.9, use getClassAttribute() instead
     */
    public function getClassAnnotation(\ReflectionClass $class, string $annotationName): ?AttributeInterface
    {
        trigger_deprecation('vich/uploader-bundle', '2.9', 'Method "%s" is deprecated, use "getClassAttribute()" instead.', __METHOD__);

        return $this->getClassAttribute($class, $annotationName);
    }

    /**
     * @deprecated since 2.9, use getMethodAttributes() instead
     *
     * @return AttributeInterface[]
     */
    public function getMethodAnnotations(\ReflectionMethod $method): array
    {
        trigger_deprecation('vich/uploader-bundle', '2.9', 'Method "%s" is deprecated, use "getMethodAttributes()" instead.', __METHOD__);

        return $this->getMethodAttributes($method);
    }

    /**
     * @deprecated since 2.9, use getMethodAttribute() instead
     */
    public function getMethodAnnotation(\ReflectionMethod $method, string $annotationName): ?AttributeInterface
    {
        trigger_deprecation('vich/uploader-bundle', '2.9', 'Method "%s" is deprecated, use "getMethodAttribute()" instead.', __METHOD__);

        return $this->getMethodAttribute($method, $annotationName);
    }

    /**
     * @deprecated since 2.9, use getPropertyAttributes() instead
     *
     * @return AttributeInterface[]
     */
    public function getPropertyAnnotations(\ReflectionProperty $property): array
    {
        trigger_deprecation('vich/uploader-bundle', '2.9', 'Method "%s" is deprecated, use "getPropertyAttributes()" instead.', __METHOD__);

        return $this->getPropertyAttributes($property);
    }

    /**
     * @deprecated since 2.9, use getPropertyAttribute() instead
     */
    public function getPropertyAnnotation(\ReflectionProperty $property, string $annotationName): ?AttributeInterface
    {
        trigger_deprecation('vich/uploader-bundle', '2.9', 'Method "%s" is deprecated, use "getPropertyAttribute()" instead.', __METHOD__);

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
