<?php

namespace Vich\UploaderBundle\Mapping;

use Doctrine\Persistence\Proxy;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\Exception\NotUploadableException;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Naming\ConfigurableInterface;
use Vich\UploaderBundle\Util\ClassUtils;

/**
 * PropertyMappingFactory.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class PropertyMappingFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var MetadataReader
     */
    protected $metadata;

    /**
     * @var array
     */
    protected $mappings;

    /**
     * @var string
     */
    protected $defaultFilenameAttributeSuffix;

    public function __construct(ContainerInterface $container, MetadataReader $metadata, array $mappings, ?string $defaultFilenameAttributeSuffix = '_name')
    {
        $this->container = $container;
        $this->metadata = $metadata;
        $this->mappings = $mappings;
        $this->defaultFilenameAttributeSuffix = $defaultFilenameAttributeSuffix;
    }

    /**
     * Creates an array of PropetyMapping objects which contain the
     * configuration for the uploadable fields in the specified
     * object.
     *
     * @param object      $obj         The object
     * @param string|null $className   The object's class. Mandatory if $obj can't be used to determine it
     * @param string|null $mappingName The mapping name
     *
     * @return array|PropertyMapping[] An array up PropertyMapping objects
     *
     * @throws \RuntimeException
     * @throws MappingNotFoundException
     * @throws NotUploadableException
     */
    public function fromObject($obj, ?string $className = null, ?string $mappingName = null): array
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

            $mappings[] = $this->createMapping($obj, $field, $mappingData);
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
    public function fromField($obj, string $field, ?string $className = null): ?PropertyMapping
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

        return $this->createMapping($obj, $field, $mappingData);
    }

    public function fromFirstField(object $obj, ?string $className = null): ?PropertyMapping
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

        return $this->createMapping($obj, \key($mappingData), \reset($mappingData));
    }

    /**
     * Checks to see if the class is uploadable.
     *
     * @param string $class The class name (FQCN)
     *
     * @throws NotUploadableException
     */
    protected function checkUploadable(string $class): void
    {
        if (!$this->metadata->isUploadable($class)) {
            throw new NotUploadableException(\sprintf('The class "%s" is not uploadable. If you use annotations to configure VichUploaderBundle, you probably just forgot to add `@Vich\Uploadable` on top of your entity. If you don\'t use annotations, check that the configuration files are in the right place. In both cases, clearing the cache can also solve the issue.', $class));
        }
    }

    /**
     * Creates the property mapping from the read annotation and configured mapping.
     *
     * @param object $obj         The object
     * @param string $fieldName   The field name
     * @param array  $mappingData The mapping data
     *
     * @return PropertyMapping The property mapping
     *
     * @throws \LogicException
     * @throws MappingNotFoundException
     */
    protected function createMapping($obj, string $fieldName, array $mappingData): PropertyMapping
    {
        if (!\array_key_exists($mappingData['mapping'], $this->mappings)) {
            throw MappingNotFoundException::createNotFoundForClassAndField($mappingData['mapping'], $this->getClassName($obj), $fieldName);
        }

        $config = $this->mappings[$mappingData['mapping']];
        $fileProperty = $mappingData['propertyName'] ?? $fieldName;
        $fileNameProperty = empty($mappingData['fileNameProperty']) ? $fileProperty.$this->defaultFilenameAttributeSuffix : $mappingData['fileNameProperty'];

        $mapping = new PropertyMapping($fileProperty, $fileNameProperty, $mappingData);
        $mapping->setMappingName($mappingData['mapping']);
        $mapping->setMapping($config);

        if (!empty($config['namer']) && null !== $config['namer']['service']) {
            $namerConfig = $config['namer'];
            $namer = $this->container->get($namerConfig['service']);

            if (!empty($namerConfig['options'])) {
                if (!$namer instanceof ConfigurableInterface) {
                    throw new \LogicException(\sprintf('Namer %s can not receive options as it does not implement ConfigurableInterface.', $namerConfig['service']));
                }
                $namer->configure($namerConfig['options']);
            }

            $mapping->setNamer($namer);
        }

        if (!empty($config['directory_namer']) && null !== $config['directory_namer']['service']) {
            $namerConfig = $config['directory_namer'];
            $namer = $this->container->get($namerConfig['service']);

            if (!empty($namerConfig['options'])) {
                if (!$namer instanceof ConfigurableInterface) {
                    throw new \LogicException(\sprintf('Namer %s can not receive options as it does not implement ConfigurableInterface.', $namerConfig['service']));
                }
                $namer->configure($namerConfig['options']);
            }

            $mapping->setDirectoryNamer($namer);
        }

        return $mapping;
    }

    /**
     * Returns the className of the given object.
     *
     * @param object|mixed $object    The object to inspect
     * @param string|null  $className User specified className
     *
     * @throws \RuntimeException
     */
    protected function getClassName($object, ?string $className = null): string
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
