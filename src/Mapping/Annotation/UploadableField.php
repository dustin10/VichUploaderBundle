<?php

namespace Vich\UploaderBundle\Mapping\Annotation;

/**
 * UploadableField.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploadableField
{
    /**
     * @var string
     */
    protected $mapping;

    /**
     * @var string
     */
    protected $fileNameProperty;

    //TODO: replace "fileNameProperty" with just "name"

    /**
     * @var string
     */
    protected $size;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var string
     */
    protected $originalName;

    /**
     * @var array
     */
    protected $dimensions;

    /**
     * Constructs a new instance of UploadableField.
     *
     * @param array $options The options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options)
    {
        if (empty($options['mapping'])) {
            throw new \InvalidArgumentException('The "mapping" attribute of UploadableField is required.');
        }

        foreach ($options as $property => $value) {
            if (!\property_exists($this, $property)) {
                throw new \RuntimeException(\sprintf('Unknown key "%s" for annotation "@%s".', $property, \get_class($this)));
            }

            $this->$property = $value;
        }
    }

    /**
     * Gets the mapping name.
     *
     * @return string The mapping name
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Gets the file name property.
     *
     * @return string The file name property
     */
    public function getFileNameProperty()
    {
        return $this->fileNameProperty;
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @return array|null
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }
}
