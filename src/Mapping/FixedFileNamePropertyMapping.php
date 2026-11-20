<?php

namespace Vich\UploaderBundle\Mapping;

use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 */
final readonly class FixedFileNamePropertyMapping implements PropertyMappingInterface
{
    public function __construct(
        private PropertyMappingInterface $mapping,
        private string $fileName,
    ) {
    }

    public function getFile(object $obj): ?File
    {
        return $this->mapping->getFile($obj);
    }

    public function setFile(object $obj, File $file): void
    {
        $this->mapping->setFile($obj, $file);
    }

    public function getFileName(object|array $obj): string
    {
        return $this->fileName;
    }

    public function setFileName(object $obj, string $value): void
    {
        $this->mapping->setFileName($obj, $value);
    }

    public function erase(object $obj): void
    {
        $this->mapping->erase($obj);
    }

    public function readProperty(object|array $obj, string $property): mixed
    {
        if ('name' === $property) {
            return $this->fileName;
        }

        return $this->mapping->readProperty($obj, $property);
    }

    public function writeProperty(object $obj, string $property, mixed $value): void
    {
        $this->mapping->writeProperty($obj, $property, $value);
    }

    public function getFilePropertyName(): string
    {
        return $this->mapping->getFilePropertyName();
    }

    public function getFileNamePropertyName(): string
    {
        return $this->mapping->getFileNamePropertyName();
    }

    public function getMappingName(): string
    {
        return $this->mapping->getMappingName();
    }

    public function getUploadName(object $obj): string
    {
        return $this->mapping->getUploadName($obj);
    }

    public function getUploadDir(object|array $obj): ?string
    {
        return $this->mapping->getUploadDir($obj);
    }

    public function getUploadDestination(): string
    {
        return $this->mapping->getUploadDestination();
    }

    public function getUriPrefix(): string
    {
        return $this->mapping->getUriPrefix();
    }
}
