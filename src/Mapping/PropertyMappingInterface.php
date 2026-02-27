<?php

namespace Vich\UploaderBundle\Mapping;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\NamerInterface;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface PropertyMappingInterface
{
    public function getFile(object $obj): ?File;

    public function setFile(object $obj, File $file): void;

    public function getFileName(object|array $obj): ?string;

    public function setFileName(object $obj, string $value): void;

    public function erase(object $obj): void;

    public function readProperty(object|array $obj, string $property): mixed;

    public function writeProperty(object $obj, string $property, mixed $value): void;

    public function getFilePropertyName(): string;

    public function getFileNamePropertyName(): string;

    public function getNamer(): ?NamerInterface;

    public function setNamer(NamerInterface $namer): void;

    public function hasNamer(): bool;

    public function getDirectoryNamer(): ?DirectoryNamerInterface;

    public function setDirectoryNamer(DirectoryNamerInterface $directoryNamer): void;

    public function hasDirectoryNamer(): bool;

    public function setMapping(array $mapping): void;

    public function getMappingName(): string;

    public function setMappingName(string $mappingName): void;

    public function getUploadName(object $obj): string;

    public function getUploadDir(object|array $obj): ?string;

    public function getUploadDestination(): string;

    public function getUriPrefix(): string;
}
