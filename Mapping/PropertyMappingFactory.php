<?php

namespace Vich\UploaderBundle\Mapping;

use Doctrine\Common\Persistence\Proxy;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Vich\UploaderBundle\Mapping\MappingReader;
use Vich\UploaderBundle\Mapping\PropertyMapping;

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
     * @var MappingReader $mapping
     */
    protected $mapping;

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
     * @param ContainerInterface $container The container.
     * @param MappingReader      $mapping   The mapping mapping.
     * @param AdapterInterface   $adapter   The adapter.
     * @param array              $mappings  The configured mappings.
     */
    public function __construct(ContainerInterface $container, MappingReader $mapping, AdapterInterface $adapter, array $mappings)
    {
        $this->container = $container;
        $this->mapping = $mapping;
        $this->adapter = $adapter;
        $this->mappings = $mappings;
    }

    /**
     * Creates an array of PropetyMapping objects which contain the
     * configuration for the uploadable fields in the specified
     * object.
     *
     * @param  object $obj The object.
     * @return array  An array up PropertyMapping objects.
     */
    public function fromObject($obj)
    {
        // @todo nothing to do here
        if ($obj instanceof Proxy) {
            $obj->__load();
        }

        $class = $this->adapter->getReflectionClass($obj);
        $this->checkUploadable($class);

        $mappings = array();
        foreach ($this->mapping->getUploadableFields($class) as $field => $mappingData) {
            $mappings[] = $this->createMapping($obj, $field, $mappingData);
        }

        return $mappings;
    }

    /**
     * Creates a property mapping object which contains the
     * configuration for the specified uploadable field.
     *
     * @param  object               $obj   The object.
     * @param  string               $field The field.
     * @return null|PropertyMapping The property mapping.
     */
    public function fromField($obj, $field)
    {
        // @todo nothing to do here
        if ($obj instanceof Proxy) {
            $obj->__load();
        }

        $class = $this->adapter->getReflectionClass($obj);
        $this->checkUploadable($class);

        $mappingData = $this->mapping->getUploadableField($class, $field);
        if ($mappingData === null) {
            return null;
        }

        return $this->createMapping($obj, $field, $mappingData);
    }

    /**
     * Checks to see if the class is uploadable.
     *
     * @param  ReflectionClass          $class The class.
     * @throws InvalidArgumentException
     */
    protected function checkUploadable(\ReflectionClass $class)
    {
        if (!$this->mapping->isUploadable($class)) {
            throw new \InvalidArgumentException('The object is not uploadable.');
        }
    }

    /**
     * Creates the property mapping from the read annotation and configured mapping.
     *
     * @param object          $obj         The object.
     * @param string          $fieldName   The field name.
     * @param UploadableField $mappingData The mapping data.
     *
     * @return PropertyMapping          The property mapping.
     * @throws InvalidArgumentException
     */
    protected function createMapping($obj, $fieldName, array $mappingData)
    {
        $class = $this->adapter->getReflectionClass($obj);

        if (!array_key_exists($mappingData['mapping'], $this->mappings)) {
            throw new \InvalidArgumentException(sprintf(
               'No mapping named "%s" configured.', $mappingData['mapping']
            ));
        }

        $config = $this->mappings[$mappingData['mapping']];

        $mapping = new PropertyMapping($mappingData['propertyName'] ?: $fieldName, $mappingData['fileNameProperty']);
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
}
