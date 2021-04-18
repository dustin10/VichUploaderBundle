<?php

namespace Vich\UploaderBundle\Mapping;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\NamerInterface;

/**
 * PropertyMapping.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class PropertyMapping
{
    /**
     * @var NamerInterface|null
     */
    protected $namer;

    /**
     * @var DirectoryNamerInterface
     */
    protected $directoryNamer;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var string
     */
    protected $mappingName;

    /**
     * @var array<string, string|null>
     */
    protected $propertyPaths = [
        'file' => null,
        'name' => null,
        'size' => null,
        'mimeType' => null,
        'originalName' => null,
        'dimensions' => null,
    ];

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

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
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|null The file
     *
     * @throws \InvalidArgumentException
     */
    public function getFile($obj): ?File
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
    public function setFile($obj, File $file): void
    {
        $this->writeProperty($obj, 'file', $file);
    }

    /**
     * Gets the fileName property of the given object.
     *
     * @param object $obj The object
     *
     * @return string The filename
     *
     * @throws \InvalidArgumentException
     */
    public function getFileName($obj): ?string
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
    public function setFileName($obj, string $value): void
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
    public function erase($obj): void
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
     * @param object $obj      The object from which read
     * @param string $property The property to read
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function readProperty($obj, $property)
    {
        if (!\array_key_exists($property, $this->propertyPaths)) {
            throw new \InvalidArgumentException(\sprintf('Unknown property %s', $property));
        }

        if (!$this->propertyPaths[$property]) {
            // not configured
            return null;
        }

        $propertyPath = $this->fixPropertyPath($obj, $this->propertyPaths[$property]);

        return $this->getAccessor()->getValue($obj, $propertyPath);
    }

    /**
     * Modifies property of the given object.
     *
     * @internal
     *
     * @param object $obj      The object to which write
     * @param string $property The property to write
     * @param mixed  $value    The value which should be written
     *
     * @throws \InvalidArgumentException
     * @throws \TypeError
     */
    public function writeProperty($obj, string $property, $value): void
    {
        if (!\array_key_exists($property, $this->propertyPaths)) {
            throw new \InvalidArgumentException(\sprintf('Unknown property %s', $property));
        }

        if (!$this->propertyPaths[$property]) {
            // not configured
            return;
        }

        $propertyPath = $this->fixPropertyPath($obj, $this->propertyPaths[$property]);
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
     * @param object $obj
     *
     * @return string The upload name
     */
    public function getUploadName($obj): string
    {
        if (!$this->hasNamer()) {
            $msg = 'Not using a namer is deprecated and will be removed in version 2.';
            @\trigger_error($msg, \E_USER_DEPRECATED);

            return $this->getFile($obj)->getClientOriginalName();
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
        $dir = $dir ? \rtrim($dir, '/\\') : $dir;

        return $dir;
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

    /**
     * Fixes a given propertyPath to make it usable both with arrays and
     * objects.
     * Ie: if the given object is in fact an array, the property path must
     * look like [myPath].
     *
     * @param object|array $object       The object to inspect
     * @param string       $propertyPath The property path to fix
     *
     * @return string The fixed property path
     */
    protected function fixPropertyPath($object, string $propertyPath): string
    {
        if (!\is_array($object)) {
            return $propertyPath;
        }

        return '[' === $propertyPath[0] ? $propertyPath : \sprintf('[%s]', $propertyPath);
    }

    protected function getAccessor(): PropertyAccessor
    {
        //TODO: reuse original property accessor from forms
        if (null !== $this->accessor) {
            return $this->accessor;
        }

        return $this->accessor = PropertyAccess::createPropertyAccessor();
    }
}
