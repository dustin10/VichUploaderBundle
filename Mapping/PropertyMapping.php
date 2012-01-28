<?php

namespace Vich\UploaderBundle\Mapping;

use Vich\UploaderBundle\Naming\NamerInterface;

/**
 * PropertyMapping.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class PropertyMapping
{
    /**
     * @var \ReflectionProperty $property
     */
    protected $property;

    /**
     * @var \ReflectionProperty $fileNameProperty
     */
    protected $fileNameProperty;

    /**
     * @var NamerInterface $namer
     */
    protected $namer;

    /**
     * @var array $mapping
     */
    protected $mapping;

    /**
     * @var string $mappingName
     */
    protected $mappingName;

    /**
     * Gets the reflection property that represents the
     * annotated property.
     *
     * @return \ReflectionProperty The property.
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Sets the reflection property that represents the annotated
     * property.
     *
     * @param \ReflectionProperty $property The reflection property.
     */
    public function setProperty(\ReflectionProperty $property)
    {
        $this->property = $property;
        $this->property->setAccessible(true);
    }

    /**
     * Gets the reflection property that represents the property
     * which holds the file name for the mapping.
     *
     * @return \ReflectionProperty The reflection property.
     */
    public function getFileNameProperty()
    {
        return $this->fileNameProperty;
    }

    /**
     * Sets the reflection property that represents the property
     * which holds the file name for the mapping.
     *
     * @param \ReflectionProperty $fileNameProperty The reflection property.
     */
    public function setFileNameProperty(\ReflectionProperty $fileNameProperty)
    {
        $this->fileNameProperty = $fileNameProperty;
        $this->fileNameProperty->setAccessible(true);
    }

    /**
     * Gets the configured namer.
     *
     * @return null|NamerInterface The namer.
     */
    public function getNamer()
    {
        return $this->namer;
    }

    /**
     * Sets the namer.
     *
     * @param \Vich\UploaderBundle\Naming\NamerInterface $namer The namer.
     */
    public function setNamer(NamerInterface $namer)
    {
        $this->namer = $namer;
    }

    /**
     * Determines if the mapping has a custom namer configured.
     *
     * @return bool True if has namer, false otherwise.
     */
    public function hasNamer()
    {
        return null !== $this->namer;
    }

    /**
     * Sets the configured configuration mapping.
     *
     * @param array $mapping The mapping;
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Gets the configured configuration mapping name.
     *
     * @return string The mapping name.
     */
    public function getMappingName()
    {
        return $this->mappingName;
    }

    /**
     * Sets the configured configuration mapping name.
     *
     * @param $mappingName The mapping name.
     */
    public function setMappingName($mappingName)
    {
        $this->mappingName = $mappingName;
    }

    /**
     * Gets the name of the annotated property.
     *
     * @return string The name.
     */
    public function getPropertyName()
    {
        return $this->property->getName();
    }

    /**
     * Gets the value of the annotated property.
     *
     * @param object $obj The object.
     * @return UploadedFile The file.
     */
    public function getPropertyValue($obj)
    {
        return $this->property->getValue($obj);
    }

    /**
     * Gets the configured file name property name.
     *
     * @return string The name.
     */
    public function getFileNamePropertyName()
    {
        return $this->fileNameProperty->getName();
    }

    /**
     * Gets the configured upload directory.
     *
     * @return string The configured upload directory.
     */
    public function getUploadDir()
    {
        return $this->mapping['upload_dir'];
    }

    /**
     * Determines if the file should be deleted upon removal of the
     * entity.
     *
     * @return bool True if delete on remove, false otherwise.
     */
    public function getDeleteOnRemove()
    {
        return $this->mapping['delete_on_remove'];
    }

    /**
     * Determines if the uploadable field should be injected with a
     * Symfony\Component\HttpFoundation\File\File instance when
     * the object is loaded from the datastore.
     *
     * @return bool True if the field should be injected, false otherwise.
     */
    public function getInjectOnLoad()
    {
        return $this->mapping['inject_on_load'];
    }
}
