<?php

namespace Vich\UploaderBundle\Mapping\Annotation;

use Vich\UploaderBundle\Mapping\AnnotationInterface;

/**
 * @deprecated since 2.9, use Vich\UploaderBundle\Mapping\Attribute\UploadableField instead.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class UploadableField implements AnnotationInterface
{
    public function __construct(
        private readonly string $mapping,
        private readonly ?string $fileNameProperty = null,
        private readonly ?string $size = null,
        private readonly ?string $mimeType = null,
        private readonly ?string $originalName = null,
        private readonly ?string $dimensions = null
    ) {
    }

    public function getMapping(): string
    {
        return $this->mapping;
    }

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
