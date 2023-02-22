<?php

namespace Vich\UploaderBundle\Mapping;

use Doctrine\Persistence\Proxy;
use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\Exception\NotUploadableException;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Util\ClassUtils;

/**
 * PropertyMappingFactory.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 *
 * @internal
 */
final class PropertyMappingFactory
{
    public function __construct(
        private readonly MetadataReader $metadata,
        private readonly PropertyMappingResolverInterface $resolver,
    ) {
    }

    /**
     * Creates an array of PropertyMapping objects which contain the
     * configuration for the uploadable fields in the specified
     * object.
     *
     * @param object|array $obj         The object
     * @param string|null  $className   The object's class. Mandatory if $obj can't be used to determine it
     * @param string|null  $mappingName The mapping name
     *
     * @return array|PropertyMapping[] An array up PropertyMapping objects
     *
     * @throws \RuntimeException
     * @throws MappingNotFoundException
     * @throws NotUploadableException
     */
    public function fromObject(object|array $obj, ?string $className = null, ?string $mappingName = null): array
    {
        if ($obj instanceof Proxy) {
            $obj->__load();
        }

        $class = $this->getClassName($obj, $className);
        $this->checkUploadable($class);

        $mappings = [];
        foreach ($this->metadata->getUploadableFields($class) as $field => $mappingData) {
            if (null !== $mappingName && $mappingName !== $mappingData['mapping']) {
                continue;
            }

            $mappings[] = $this->resolver->resolve($obj, $field, $mappingData);
        }

        return $mappings;
    }

    /**
     * Creates a property mapping object which contains the
     * configuration for the specified uploadable field.
     *
     * @param object|array $obj       The object
     * @param string       $field     The field
     * @param string|null  $className The object's class. Mandatory if $obj can't be used to determine it
     *
     * @return PropertyMapping|null The property mapping
     *
     * @throws \RuntimeException
     * @throws MappingNotFoundException
     * @throws NotUploadableException
     */
    public function fromField(object|array $obj, string $field, ?string $className = null): ?PropertyMapping
    {
        if ($obj instanceof Proxy) {
            $obj->__load();
        }

        $class = $this->getClassName($obj, $className);
        $this->checkUploadable($class);

        $mappingData = $this->metadata->getUploadableField($class, $field);
        if (null === $mappingData) {
            return null;
        }

        return $this->resolver->resolve($obj, $field, $mappingData);
    }

    public function fromFirstField(object|array $obj, ?string $className = null): ?PropertyMapping
    {
        if ($obj instanceof Proxy) {
            $obj->__load();
        }

        $class = $this->getClassName($obj, $className);
        $this->checkUploadable($class);

        $mappingData = $this->metadata->getUploadableFields($class);
        if (0 === \count($mappingData)) {
            return null;
        }

        return $this->resolver->resolve($obj, \key($mappingData), \reset($mappingData));
    }

    /**
     * Checks to see if the class is uploadable.
     *
     * @param string $class The class name (FQCN)
     *
     * @throws NotUploadableException
     */
    private function checkUploadable(string $class): void
    {
        if (!$this->metadata->isUploadable($class)) {
            throw new NotUploadableException(\sprintf('The class "%s" is not uploadable. If you use attributes to configure VichUploaderBundle, you probably just forgot to add `#[Vich\Uploadable]` on top of your entity. If you don\'t use attributes, check that the configuration files are in the right place. In both cases, clearing the cache can also solve the issue.', $class));
        }
    }

    /**
     * Returns the className of the given object.
     *
     * @param object|mixed $object    The object to inspect
     * @param string|null  $className User specified className
     *
     * @throws \RuntimeException
     */
    private function getClassName($object, ?string $className = null): string
    {
        if (null !== $className) {
            return $className;
        }

        if (\is_object($object)) {
            return ClassUtils::getClass($object);
        }

        throw new \RuntimeException('Impossible to determine the class name. Either specify it explicitly or give an object');
    }
}
