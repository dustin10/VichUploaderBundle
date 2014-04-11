<?php

namespace Vich\UploaderBundle\Mapping;

use Doctrine\Common\Persistence\Proxy;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Vich\UploaderBundle\Metadata\MetadataReader;

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
     * @var AdapterInterface $adapter
     */
    protected $adapter;

    /**
     * @var array $mappings
     */
    protected $mappings;

    /**
     * Constructs a new instance of PropertyMappingFactory.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container The container.
     * @param \Vich\UploaderBundle\Metadata\MetadataReader              $metadata  The mapping mapping.
     * @param \Vich\UploaderBundle\Adapter\AdapterInterface             $adapter   The adapter.
     * @param array                                                     $mappings  The configured mappings.
     */
    public function __construct(ContainerInterface $container, MetadataReader $metadata, AdapterInterface $adapter, array $mappings)
    {
        $this->container = $container;
        $this->metadata = $metadata;
        $this->adapter = $adapter;
        $this->mappings = $mappings;
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
     * @param object                                          $obj         The object.
     * @param string                                          $fieldName   The field name.
     * @param \Vich\UploaderBundle\Annotation\UploadableField $mappingData The mapping data.
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
     */
    protected function getClassName($object, $className = null)
    {
        if ($className !== null) {
            return $className;
        }

        if (is_object($object)) {
            return $this->adapter->getClassName($object);
        }

        throw new \RuntimeException('Impossible to determine the class name. Either specify it explicitly or give an object');
    }
}
