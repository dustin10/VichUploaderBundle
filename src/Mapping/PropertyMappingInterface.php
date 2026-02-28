<?php

namespace Vich\UploaderBundle\Mapping;

use Symfony\Component\HttpFoundation\File\File;

interface PropertyMappingInterface
{
    /**
     * Gets the file property value for the given object.
     *
     * @param object $obj The object
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|\Vich\UploaderBundle\FileAbstraction\ReplacingFile|null The file
     *
     * @throws \InvalidArgumentException
     */
    public function getFile(object $obj): ?File;

    /**
     * Modifies the file property value for the given object.
     *
     * @param object $obj  The object
     * @param File   $file The new file
     *
     * @throws \InvalidArgumentException
     * @throws \TypeError
     */
    public function setFile(object $obj, File $file): void;

    /**
     * Gets the fileName property of the given object.
     *
     * @param object|array $obj The object or array
     *
     * @return string|null The filename
     *
     * @throws \InvalidArgumentException
     */
    public function getFileName(object|array $obj): ?string;

    /**
     * Modifies the fileName property of the given object.
     *
     * @param object $obj The object
     *
     * @throws \InvalidArgumentException
     * @throws \TypeError
     */
    public function setFileName(object $obj, string $value): void;

    /**
     * Removes value for each file-related property of the given object.
     *
     * @param object $obj The object
     *
     * @throws \InvalidArgumentException
     * @throws \TypeError
     */
    public function erase(object $obj): void;

    /**
     * Reads property of the given object.
     *
     * @internal
     *
     * @param object|array $obj      The object or array from which read
     * @param string       $property The property to read
     *
     * @throws \InvalidArgumentException
     */
    public function readProperty(object|array $obj, string $property): mixed;

    /**
     * Modifies property of the given object.
     *
     * @param object $obj      The object to which write
     * @param string $property The property to write
     * @param mixed  $value    The value which should be written
     *
     * @throws \InvalidArgumentException
     * @throws \TypeError
     *
     * @internal
     */
    public function writeProperty(object $obj, string $property, mixed $value): void;

    /**
     * Get the configured file property name.
     *
     * @return string The name
     */
    public function getFilePropertyName(): string;

    /**
     * Get the configured filename property name.
     *
     * @return string The name
     */
    public function getFileNamePropertyName(): string;

    /**
     * Get the configured configuration mapping name.
     */
    public function getMappingName(): string;

    /**
     * Get the upload name for a given file (uses The file namers).
     *
     * @return string The upload name
     */
    public function getUploadName(object $obj): string;

    /**
     * Get the upload directory for a given file (uses the directory namers).
     *
     * @return string|null The upload directory
     */
    public function getUploadDir(object|array $obj): ?string;

    /**
     * Get the base upload directory.
     *
     * @return string The configured upload directory
     */
    public function getUploadDestination(): string;

    public function getUriPrefix(): string;
}
