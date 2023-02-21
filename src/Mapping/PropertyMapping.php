<?php

namespace Vich\UploaderBundle\Mapping;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Util\PropertyPathUtils;

/**
 * PropertyMapping.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class PropertyMapping
{
    private ?NamerInterface $namer = null;

    private ?DirectoryNamerInterface $directoryNamer = null;

    private ?array $mapping = null;

    private ?string $mappingName = null;

    /**
     * @var array<string, string|null>
     */
    private array $propertyPaths = [
        'file' => null,
        'name' => null,
        'size' => null,
        'mimeType' => null,
        'originalName' => null,
        'dimensions' => null,
    ];

    private ?PropertyAccessor $accessor = null;

    /**
     * @param string         $filePropertyPath     The path to the "file" property
     * @param string         $fileNamePropertyPath The path to the "filename" property
     * @param array|string[] $propertyPaths        The paths to other properties
     */
    public function __construct(string $filePropertyPath, string $fileNamePropertyPath, array $propertyPaths = [])
    {
        $this->propertyPaths = \array_merge(
            $this->propertyPaths,
            ['file' => $filePropertyPath, 'name' => $fileNamePropertyPath],
            $propertyPaths
        );
    }

    /**
     * Gets the file property value for the given object.
     *
     * @param object $obj The object
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|\Vich\UploaderBundle\FileAbstraction\ReplacingFile|null The file
     *
     * @throws \InvalidArgumentException
     */
    public function getFile(object $obj): ?File
    {
        return $this->readProperty($obj, 'file');
    }

    /**
     * Modifies the file property value for the given object.
     *
     * @param object $obj  The object
     * @param File   $file The new file
     *
     * @throws \InvalidArgumentException
     * @throws \TypeError
     */
    public function setFile(object $obj, File $file): void
    {
        $this->writeProperty($obj, 'file', $file);
    }

    /**
     * Gets the fileName property of the given object.
     *
     * @param object|array $obj The object or array
     *
     * @return string|null The filename
     *
     * @throws \InvalidArgumentException
     */
    public function getFileName(object|array $obj): ?string
    {
        return $this->readProperty($obj, 'name');
    }

    /**
     * Modifies the fileName property of the given object.
     *
     * @param object $obj The object
     *
     * @throws \InvalidArgumentException
     * @throws \TypeError
     */
    public function setFileName(object $obj, string $value): void
    {
        $this->writeProperty($obj, 'name', $value);
    }

    /**
     * Removes value for each file-related property of the given object.
     *
     * @param object $obj The object
     *
     * @throws \InvalidArgumentException
     * @throws \TypeError
     */
    public function erase(object $obj): void
    {
        foreach (['name', 'size', 'mimeType', 'originalName', 'dimensions'] as $property) {
            $this->writeProperty($obj, $property, null);
        }
    }

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
    public function readProperty(object|array $obj, string $property): mixed
    {
        if (!\array_key_exists($property, $this->propertyPaths)) {
            throw new \InvalidArgumentException(\sprintf('Unknown property %s', $property));
        }

        if (!$this->propertyPaths[$property]) {
            // not configured
            return null;
        }

        $propertyPath = PropertyPathUtils::fixPropertyPath($obj, $this->propertyPaths[$property]);

        return $this->getAccessor()->getValue($obj, $propertyPath);
    }

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
    public function writeProperty(object $obj, string $property, mixed $value): void
    {
        if (!\array_key_exists($property, $this->propertyPaths)) {
            throw new \InvalidArgumentException(\sprintf('Unknown property %s', $property));
        }

        if (!$this->propertyPaths[$property]) {
            // not configured
            return;
        }

        $propertyPath = PropertyPathUtils::fixPropertyPath($obj, $this->propertyPaths[$property]);
        $this->getAccessor()->setValue($obj, $propertyPath, $value);
    }

    /**
     * Gets the configured file property name.
     *
     * @return string The name
     */
    public function getFilePropertyName(): string
    {
        return $this->propertyPaths['file'];
    }

    /**
     * Gets the configured filename property name.
     *
     * @return string The name
     */
    public function getFileNamePropertyName(): string
    {
        return $this->propertyPaths['name'];
    }

    /**
     * Gets the configured namer.
     */
    public function getNamer(): ?NamerInterface
    {
        return $this->namer;
    }

    /**
     * Sets the namer.
     */
    public function setNamer(NamerInterface $namer): void
    {
        $this->namer = $namer;
    }

    /**
     * Determines if the mapping has a custom namer configured.
     */
    public function hasNamer(): bool
    {
        return null !== $this->namer;
    }

    /**
     * Gets the configured directory namer.
     */
    public function getDirectoryNamer(): ?DirectoryNamerInterface
    {
        return $this->directoryNamer;
    }

    /**
     * Sets the directory namer.
     */
    public function setDirectoryNamer(DirectoryNamerInterface $directoryNamer): void
    {
        $this->directoryNamer = $directoryNamer;
    }

    /**
     * Determines if the mapping has a custom directory namer configured.
     */
    public function hasDirectoryNamer(): bool
    {
        return null !== $this->directoryNamer;
    }

    /**
     * Sets the configured configuration mapping.
     *
     * @param array $mapping The mapping;
     */
    public function setMapping(array $mapping): void
    {
        $this->mapping = $mapping;
    }

    /**
     * Gets the configured configuration mapping name.
     */
    public function getMappingName(): string
    {
        return $this->mappingName;
    }

    /**
     * Sets the configured configuration mapping name.
     *
     * @param string $mappingName
     */
    public function setMappingName($mappingName): void
    {
        $this->mappingName = $mappingName;
    }

    /**
     * Gets the upload name for a given file (uses The file namers).
     *
     * @return string The upload name
     */
    public function getUploadName(object $obj): string
    {
        if (!$this->hasNamer()) {
            throw new \RuntimeException('A namer must be configured.');
        }

        return $this->getNamer()->name($obj, $this);
    }

    /**
     * Gets the upload directory for a given file (uses the directory namers).
     *
     * @param object $obj
     *
     * @return string|null The upload directory
     */
    public function getUploadDir($obj): ?string
    {
        if (!$this->hasDirectoryNamer()) {
            return '';
        }

        $dir = $this->getDirectoryNamer()->directoryName($obj, $this);

        // strip the trailing directory separator if needed
        return $dir ? \rtrim($dir, '/\\') : $dir;
    }

    /**
     * Gets the base upload directory.
     *
     * @return string The configured upload directory
     */
    public function getUploadDestination(): string
    {
        return $this->mapping['upload_destination'];
    }

    /**
     * Get uri prefix.
     */
    public function getUriPrefix(): string
    {
        return $this->mapping['uri_prefix'];
    }

    private function getAccessor(): PropertyAccessor
    {
        // TODO: reuse original property accessor from forms
        if (null !== $this->accessor) {
            return $this->accessor;
        }

        return $this->accessor = PropertyAccess::createPropertyAccessor();
    }
}
