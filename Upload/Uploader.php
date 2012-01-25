<?php

namespace Vich\UploaderBundle\Upload;

use Vich\UploaderBundle\Upload\UploaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Driver\AnnotationDriver;
use Vich\UploaderBundle\Adapter\AdapterInterface;

/**
 * Uploader.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class Uploader implements UploaderInterface
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
     * @var string $webDirName
     */
    protected $webDirName;

    /**
     * Constructs a new instance of Uploader.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container The container.
     * @param \Vich\UploaderBundle\Driver\AnnotationDriver $driver The driver.
     * @param \Vich\UploaderBundle\Adapter\AdapterInterface $adapter The adapter.
     * @param array $mappings The configured mappings.
     * @param $webDirName The name of the application's public directory.
     */
    public function __construct(ContainerInterface $container, AnnotationDriver $driver, AdapterInterface $adapter, array $mappings, $webDirName)
    {
        $this->container = $container;
        $this->driver = $driver;
        $this->adapter = $adapter;
        $this->mappings = $mappings;
        $this->webDirName = $webDirName;
    }
    
    /**
     * {@inheritDoc}
     */
    public function upload($obj)
    {
        $class = $this->adapter->getReflectionClass($obj);
        $uploadableFields = $this->driver->readUploadableFields($class);

        foreach($uploadableFields as $uploadableField) {
            $mapping = $this->getMapping($uploadableField->getMapping());

            $prop = $class->getProperty($uploadableField->getPropertyName());
            $prop->setAccessible(true);

            $file = $prop->getValue($obj);
            if (is_null($file) || !$file instanceof UploadedFile) {
                continue;
            }

            $uploadDir = $mapping['upload_dir'];

            if (!isset($mapping['namer'])) {
                $name = $file->getClientOriginalName();
            } else {
                $namer = $this->container->get($mapping['namer']);
                $name = $namer->name($obj);
            }

            $file->move($uploadDir, $name);

            $nameProp = $class->getProperty($uploadableField->getFileNameProperty());
            $nameProp->setAccessible(true);
            $nameProp->setValue($obj, $name);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function populateUploadableFields($obj) {
        $class = $this->adapter->getReflectionClass($obj);
        $uploadableFields = $this->driver->readUploadableFields($class);

        foreach ($uploadableFields as $uploadableField) {
            $mapping = $this->getMapping($uploadableField->getMapping());
            $dir = $mapping['upload_dir'];

            $nameProp = $class->getProperty($uploadableField->getFileNameProperty());
            $nameProp->setAccessible(true);
            $name = $nameProp->getValue($obj);

            if (is_null($name)) {
                continue;
            }

            $prop = $class->getProperty($uploadableField->getPropertyName());
            $prop->setAccessible(true);
            $prop->setValue($obj, new File(sprintf('%s/%s', $dir, $name), false));
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function remove($obj)
    {
        $class = $this->adapter->getReflectionClass($obj);
        $uploadableFields = $this->driver->readUploadableFields($class);

        foreach ($uploadableFields as $uploadableField) {
            $mapping = $this->getMapping($uploadableField->getMapping());

            if ($mapping['delete_on_remove']) {
                $dir = $mapping['upload_dir'];

                $prop = $class->getProperty($uploadableField->getFileNameProperty());
                $prop->setAccessible(true);
                $name = $prop->getValue($obj);

                unlink(sprintf('%s/%s', $dir, $name));
            }
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function getPublicPath($obj, $field)
    {
        $class = $this->adapter->getReflectionClass($obj);
        $uploadableFields = $this->driver->readUploadableFields($class);

        foreach ($uploadableFields as $uploadableField) {
            if ($uploadableField->getPropertyName() === $field) {
                $mapping = $this->getMapping($uploadableField->getMapping());

                $uploadDir = $mapping['upload_dir'];
                $index = strpos($uploadDir, $this->webDirName);
                $relDir = substr($uploadDir, $index + strlen($this->webDirName));

                $prop = $class->getProperty($uploadableField->getFileNameProperty());
                $prop->setAccessible(true);
                $name = $prop->getValue($obj);

                return sprintf('%s/%s', $relDir, $name);
            }
        }

        throw new \InvalidArgumentException(
            sprintf('Unable to fine uploadable field named: "%s"', $field)
        );
    }
    
    /**
     * Gets the configured mappings for the specified object.
     * 
     * @param string $name The mapping name.
     * @return array The mappings for the specified object.
     */
    protected function getMapping($name)
    {
        if (!array_key_exists($name, $this->mappings)) {
            throw new \InvalidArgumentException(sprintf(
                'No mapping found named: "%s"',
                $name
            ));
        }

        return $this->mappings[$name];
    }
}
