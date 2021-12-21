<?php

namespace Vich\UploaderBundle\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Vich\UploaderBundle\Mapping\AnnotationInterface;

/**
 * UploadableField.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 * @NamedArgumentConstructor
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 * @final
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
     * @var string
     */
    protected $dimensions;

    /**
     * Constructs a new instance of UploadableField.
     */
    public function __construct(
        string $mapping,
        string $fileNameProperty = null,
        string $size = null,
        string $mimeType = null,
        string $originalName = null,
        string $dimensions = null
    ) {
        $this->mapping = $mapping;
        $this->fileNameProperty = $fileNameProperty;
        $this->size = $size;
        $this->mimeType = $mimeType;
        $this->originalName = $originalName;
        $this->dimensions = $dimensions;
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

    public function getDimensions(): ?string
    {
        return $this->dimensions;
    }
}
