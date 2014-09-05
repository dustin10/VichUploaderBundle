<?php

namespace Vich\UploaderBundle\Mapping;

use Doctrine\Common\Persistence\Proxy;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Vich\UploaderBundle\Exception\MissingMappingException;
use Vich\UploaderBundle\Metadata\MetadataReader;
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
     * Constructs a new instance of PropertyMappingFactory.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container The container.
     * @param \Vich\UploaderBundle\Metadata\MetadataReader              $metadata  The mapping mapping.
     * @param array                                                     $mappings  The configured mappings.
     */
    public function __construct(ContainerInterface $container, MetadataReader $metadata, array $mappings)
    {
        $this->container = $container;
        $this->metadata = $metadata;
        $this->mappings = $mappings;
    }

    /**
     * Creates PropetyMapping from its name.
     *
     * @param object $object      The object.
     * @param string $mappingName The mapping name.
     * @param string $className   The object's class. Mandatory if $obj can't be used to determine it.
     *
     * @throws \Vich\UploaderBundle\Exception\MissingMappingException
     * @return PropertyMapping
     */
    public function fromName($object, $mappingName, $className = null)
    {
        $mappings = $this->fromObject($object, $className);

        if (!isset($mappings[$mappingName])) {
            throw new MissingMappingException(sprintf('Mapping %s does not exist', $mappingName));
        }

        return $mappings[$mappingName];
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
    public function fromObject($obj, $className = null)
    {
        if ($obj instanceof Proxy) {
            $obj->__load();
        }

        $class = $this->getClassName($obj, $className);
        $this->checkUploadable($class);

        $mappings = array();
        foreach ($this->metadata->getUploadableFields($class) as $field => $mappingData) {
            $mappings[$mappingData['mapping']] = $this->createMapping($obj, $field, $mappingData);
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
     * @throws \InvalidArgumentException
     */
    protected function checkUploadable($class)
    {
        if (!$this->metadata->isUploadable($class)) {
            throw new \InvalidArgumentException('The object is not uploadable.');
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
     * @throws \InvalidArgumentException
     */
    protected function createMapping($obj, $fieldName, array $mappingData)
    {
        if (!array_key_exists($mappingData['mapping'], $this->mappings)) {
            throw new \InvalidArgumentException(sprintf(
               'No mapping named "%s" configured.', $mappingData['mapping']
            ));
        }

        $config = $this->mappings[$mappingData['mapping']];

        $mapping = new PropertyMapping(isset($mappingData['propertyName']) ? $mappingData['propertyName'] : $fieldName, $mappingData['fileNameProperty']);
        $mapping->setMappingName($mappingData['mapping']);
        $mapping->setMapping($config);

        if ($config['namer']) {
            $mapping->setNamer($this->container->get($config['namer']));
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
