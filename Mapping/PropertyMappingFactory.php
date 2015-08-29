<?php

namespace Vich\UploaderBundle\Mapping;

use Doctrine\Common\Persistence\Proxy;
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
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var MetadataReader $metadata
     */
    protected $metadata;

    /**
     * @var array $mappings
     */
    protected $mappings;

    /**
     * @var string $defaultFilenameAttributeSuffix
     */
    protected $defaultFilenameAttributeSuffix;

    /**
     * Constructs a new instance of PropertyMappingFactory.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container                      The container.
     * @param \Vich\UploaderBundle\Metadata\MetadataReader              $metadata                       The mapping mapping.
     * @param array                                                     $mappings                       The configured mappings.
     * @param string                                                    $defaultFilenameAttributeSuffix The default suffix to be used if the fileNamePropertyPath isn't given for a mapping.
     */
    public function __construct(ContainerInterface $container, MetadataReader $metadata, array $mappings, $defaultFilenameAttributeSuffix = '_name')
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
     * @param object $obj       The object.
     * @param string $className The object's class. Mandatory if $obj can't be used to determine it.
     *
     * @return array An array up PropertyMapping objects.
     */
    public function fromObject($obj, $className = null, $mappingName = null)
    {
        if ($obj instanceof Proxy) {
            $obj->__load();
        }

        $class = $this->getClassName($obj, $className);
        $this->checkUploadable($class);

        $mappings = array();
        foreach ($this->metadata->getUploadableFields($class) as $field => $mappingData) {
            if ($mappingName !== null && $mappingName !== $mappingData['mapping']) {
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
     * @param object $obj       The object.
     * @param string $field     The field.
     * @param string $className The object's class. Mandatory if $obj can't be used to determine it.
     *
     * @return null|PropertyMapping The property mapping.
     */
    public function fromField($obj, $field, $className = null)
    {
        if ($obj instanceof Proxy) {
            $obj->__load();
        }

        $class = $this->getClassName($obj, $className);
        $this->checkUploadable($class);

        $mappingData = $this->metadata->getUploadableField($class, $field);
        if ($mappingData === null) {
            return null;
        }

        return $this->createMapping($obj, $field, $mappingData);
    }

    /**
     * Checks to see if the class is uploadable.
     *
     * @param string $class The class name (FQCN).
     *
     * @throws NotUploadableException
     */
    protected function checkUploadable($class)
    {
        if (!$this->metadata->isUploadable($class)) {
            throw new NotUploadableException(sprintf('The class "%s" is not uploadable. If you use annotations to configure VichUploaderBundle, you probably just forgot to add `@Vich\Uploadable` on top of your entity. If you don\'t use annotations, check that the configuration files are in the right place. In both cases, clearing the cache can also solve the issue.', $class));
        }
    }

    /**
     * Creates the property mapping from the read annotation and configured mapping.
     *
     * @param object $obj         The object.
     * @param string $fieldName   The field name.
     * @param array  $mappingData The mapping data.
     *
     * @return PropertyMapping           The property mapping.
     * @throws MappingNotFoundException
     */
    protected function createMapping($obj, $fieldName, array $mappingData)
    {
        if (!array_key_exists($mappingData['mapping'], $this->mappings)) {
            throw MappingNotFoundException::createNotFoundForClassAndField($mappingData['mapping'], $this->getClassName($obj), $fieldName);
        }

        $config = $this->mappings[$mappingData['mapping']];
        $fileProperty = isset($mappingData['propertyName']) ? $mappingData['propertyName'] : $fieldName;
        $fileNameProperty = empty($mappingData['fileNameProperty']) ? $fileProperty . $this->defaultFilenameAttributeSuffix : $mappingData['fileNameProperty'];

        $mapping = new PropertyMapping($fileProperty, $fileNameProperty);
        $mapping->setMappingName($mappingData['mapping']);
        $mapping->setMapping($config);

        if ($config['namer']['service']) {
            $namerConfig = $config['namer'];
            $namer       = $this->container->get($namerConfig['service']);

            if (!empty($namerConfig['options']) && $namer instanceof ConfigurableInterface) {
                $namer->configure($namerConfig['options']);
            } else if (!empty($namerConfig['options']) && !$namer instanceof ConfigurableInterface) {
                throw new \LogicException(sprintf('Namer %s can not receive options as it does not implement ConfigurableInterface.', $namerConfig['service']));
            }

            $mapping->setNamer($namer);
        }

        if ($config['directory_namer']) {
            $mapping->setDirectoryNamer($this->container->get($config['directory_namer']));
        }

        return $mapping;
    }

    /**
     * Returns the className of the given object.
     *
     * @param object $object    The object to inspect.
     * @param string $className User specified className.
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getClassName($object, $className = null)
    {
        if ($className !== null) {
            return $className;
        }

        if (is_object($object)) {
            return ClassUtils::getClass($object);
        }

        throw new \RuntimeException('Impossible to determine the class name. Either specify it explicitly or give an object');
    }
}
