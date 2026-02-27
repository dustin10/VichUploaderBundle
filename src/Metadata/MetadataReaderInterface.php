<?php

namespace Vich\UploaderBundle\Metadata;

/**
 * @internal
 */
interface MetadataReaderInterface
{
    public function isUploadable(string $class, ?string $mapping = null): bool;

    public function getUploadableClasses(): array;

    public function getUploadableFields(string $class, ?string $mapping = null): array;

    public function getUploadableField(string $class, string $field): mixed;
}
