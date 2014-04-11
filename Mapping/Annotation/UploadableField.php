<?php

namespace Vich\UploaderBundle\Mapping\Annotation;

/**
 * UploadableField.
 *
 * @Annotation
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploadableField
{
    /**
     * @var string $mapping
     */
    protected $mapping;

    /**
     * @var string $name
     */
    protected $propertyName;

    /**
     * @var string $fileNameProperty
     */
    protected $fileNameProperty;

    /**
     * Constructs a new instance of UploadableField.
     *
     * @param  array                     $options The options.
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options)
    {
        if (isset($options['mapping'])) {
            $this->mapping = $options['mapping'];
        } else {
            throw new \InvalidArgumentException('The "mapping" attribute of UploadableField is required.');
        }

        if (isset($options['fileNameProperty'])) {
            $this->fileNameProperty = $options['fileNameProperty'];
        } else {
            throw new \InvalidArgumentException('The "fileNameProperty" attribute of UploadableField is required.');
        }
    }

    /**
     * Gets the mapping name.
     *
     * @return string The mapping name.
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Sets the mapping name.
     *
     * @param $mapping The mapping name.
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Gets the property name.
     *
     * @return string The property name.
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Sets the property name.
     *
     * @param $propertyName The property name.
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Gets the file name property.
     *
     * @return string The file name property.
     */
    public function getFileNameProperty()
    {
        return $this->fileNameProperty;
    }

    /**
     * Sets the file name property.
     *
     * @param $fileNameProperty The file name property.
     */
    public function setFileNameProperty($fileNameProperty)
    {
        $this->fileNameProperty = $fileNameProperty;
    }
}
