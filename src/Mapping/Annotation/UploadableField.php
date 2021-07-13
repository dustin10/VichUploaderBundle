<?php

namespace Vich\UploaderBundle\Mapping\Annotation;

use Vich\UploaderBundle\Mapping\AnnotationInterface;

/**
 * UploadableField.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 * @final
 *
 * @internal
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class UploadableField implements AnnotationInterface
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
    public function __construct(
        array $options = [],
        string $mapping = null,
        string $fileNameProperty = null,
        string $size = null,
        string $mimeType = null,
        string $originalName = null,
        array $dimensions = null
    ) {
        if (empty($options['mapping']) && empty($mapping)) {
            throw new \InvalidArgumentException('The "mapping" attribute of UploadableField is required.');
        }

        $this->mapping = $mapping;
        $this->fileNameProperty = $fileNameProperty;
        $this->size = $size;
        $this->mimeType = $mimeType;
        $this->originalName = $originalName;
        $this->dimensions = $dimensions;

        foreach ($options as $property => $value) {
            if (!\property_exists($this, $property)) {
                throw new \RuntimeException(\sprintf('Unknown key "%s" for annotation "@%s".', $property, static::class));
            }

            $this->$property = $value;
        }
    }

    /**
     * Gets the mapping name.
     *
     * @return string The mapping name
     */
    public function getMapping(): string
    {
        return $this->mapping;
    }

    /**
     * Gets the file name property.
     *
     * @return string|null The file name property
     */
    public function getFileNameProperty(): ?string
    {
        return $this->fileNameProperty;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }
}
