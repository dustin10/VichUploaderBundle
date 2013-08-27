<?php

namespace Vich\UploaderBundle\Mapping;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Driver\AnnotationDriver;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Doctrine\Common\Persistence\Proxy;

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
     * @var AnnotationDriver $driver
     */
    protected $driver;

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
     * @param \Vich\UploaderBundle\Driver\AnnotationDriver              $driver    The driver.
     * @param \Vich\UploaderBundle\Adapter\AdapterInterface             $adapter   The adapter.
     * @param array                                                     $mappings  The configured mappings.
     */
    public function __construct(ContainerInterface $container, AnnotationDriver $driver, AdapterInterface $adapter, array $mappings)
    {
        $this->container = $container;
        $this->driver = $driver;
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
        if ($obj instanceof Proxy) {
            $obj->__load();
        }
        $class = $this->adapter->getReflectionClass($obj);
        $this->checkUploadable($class);

        $mappings = array();
        foreach ($this->driver->readUploadableFields($class) as $field) {
            $mappings[] = $this->createMapping($obj, $field);
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
        if ($obj instanceof Proxy) {
            $obj->__load();
        }
        $class = $this->adapter->getReflectionClass($obj);
        $this->checkUploadable($class);

        $annot = $this->driver->readUploadableField($class, $field);
        if (null === $annot) {
            return null;
        }

        return $this->createMapping($obj, $annot);
    }

    /**
     * Checks to see if the class is uploadable.
     *
     * @param  \ReflectionClass          $class The class.
     * @throws \InvalidArgumentException
     */
    protected function checkUploadable(\ReflectionClass $class)
    {
        if (null === $this->driver->readUploadable($class)) {
            throw new \InvalidArgumentException(
                'The object is not uploadable.'
            );
        }
    }

    /**
     * Creates the property mapping from the read annotation and configured mapping.
     *
     * @param  object                                          $obj   The object.
     * @param  \Vich\UploaderBundle\Annotation\UploadableField $field The read annotation.
     * @return PropertyMapping                                 The property mapping.
     * @throws \InvalidArgumentException
     */
    protected function createMapping($obj, UploadableField $field)
    {
        $class = $this->adapter->getReflectionClass($obj);

        if (!array_key_exists($field->getMapping(), $this->mappings)) {
            throw new \InvalidArgumentException(sprintf(
               'No mapping named "%s" configured.', $field->getMapping()
            ));
        }

        $config = $this->mappings[$field->getMapping()];

        $mapping = new PropertyMapping();
        $mapping->setProperty($class->getProperty($field->getPropertyName()));
        $mapping->setFileNameProperty($class->getProperty($field->getFileNameProperty()));
        $mapping->setMappingName($field->getMapping());
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
