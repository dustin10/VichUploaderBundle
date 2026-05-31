<?php

namespace Vich\UploaderBundle\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Vich\UploaderBundle\Mapping\AnnotationInterface;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * @NamedArgumentConstructor
 *
 * @deprecated since 2.9, use Vich\UploaderBundle\Mapping\Attribute\UploadableField instead
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
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
        trigger_deprecation('vich/uploader-bundle', '2.9', 'The "Vich\UploaderBundle\Mapping\Annotation\UploadableField" class is deprecated, use "Vich\UploaderBundle\Mapping\Attribute\UploadableField" instead.');
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
