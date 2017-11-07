<?php

namespace Vich\UploaderBundle\Mapping;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccess;
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
     * @var NamerInterface
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
     * @var string[]
     */
    protected $propertyPaths = [
        'file' => null,
        'name' => null,
        'size' => null,
        'mimeType' => null,
        'originalName' => null,
    ];

    /**
     * @var PropertyAccess
     */
    protected $accessor;

    /**
     * @param string   $filePropertyPath     The path to the "file" property
     * @param string   $fileNamePropertyPath The path to the "filename" property
     * @param string[] $propertyPaths        The paths to the "size", "mimeType" and "originalName" properties
     */
    public function __construct($filePropertyPath, $fileNamePropertyPath, array $propertyPaths = [])
    {
        $this->propertyPaths = array_merge(
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
     * @return UploadedFile The file
     */
    public function getFile($obj)
    {
        return $this->readProperty($obj, 'file');
    }

    /**
     * Modifies the file property value for the given object.
     *
     * @param object       $obj  The object
     * @param UploadedFile $file The new file
     */
    public function setFile($obj, $file)
    {
        $this->writeProperty($obj, 'file', $file);
    }

    /**
     * Gets the fileName property of the given object.
     *
     * @param object $obj The object
     *
     * @return string The filename
     */
    public function getFileName($obj)
    {
        return $this->readProperty($obj, 'name');
    }

    /**
     * Modifies the fileName property of the given object.
     *
     * @param object $obj   The object
     * @param string $value
     */
    public function setFileName($obj, $value)
    {
        $this->writeProperty($obj, 'name', $value);
    }

    /**
     * Removes value for each file-related property of the given object.
     *
     * @param object $obj The object
     */
    public function erase($obj)
    {
        foreach (['name', 'size', 'mimeType', 'originalName'] as $property) {
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
     */
    public function readProperty($obj, $property)
    {
        if (!array_key_exists($property, $this->propertyPaths)) {
            throw new \InvalidArgumentException(sprintf('Unknown property %s', $property));
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
     */
    public function writeProperty($obj, $property, $value)
    {
        if (!array_key_exists($property, $this->propertyPaths)) {
            throw new \InvalidArgumentException(sprintf('Unknown property %s', $property));
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
    public function getFilePropertyName()
    {
        return $this->propertyPaths['file'];
    }

    /**
     * Gets the configured filename property name.
     *
     * @return string The name
     */
    public function getFileNamePropertyName()
    {
        return $this->propertyPaths['name'];
    }

    /**
     * Gets the configured namer.
     *
     * @return NamerInterface|null The namer
     */
    public function getNamer()
    {
        return $this->namer;
    }

    /**
     * Sets the namer.
     *
     * @param NamerInterface $namer The namer
     */
    public function setNamer(NamerInterface $namer)
    {
        $this->namer = $namer;
    }

    /**
     * Determines if the mapping has a custom namer configured.
     *
     * @return bool True if has namer, false otherwise
     */
    public function hasNamer()
    {
        return null !== $this->namer;
    }

    /**
     * Gets the configured directory namer.
     *
     * @return DirectoryNamerInterface|null The directory namer
     */
    public function getDirectoryNamer()
    {
        return $this->directoryNamer;
    }

    /**
     * Sets the directory namer.
     *
     * @param DirectoryNamerInterface $directoryNamer The directory namer
     */
    public function setDirectoryNamer(DirectoryNamerInterface $directoryNamer)
    {
        $this->directoryNamer = $directoryNamer;
    }

    /**
     * Determines if the mapping has a custom directory namer configured.
     *
     * @return bool True if has directory namer, false otherwise
     */
    public function hasDirectoryNamer()
    {
        return null !== $this->directoryNamer;
    }

    /**
     * Sets the configured configuration mapping.
     *
     * @param array $mapping The mapping;
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Gets the configured configuration mapping name.
     *
     * @return string The mapping name
     */
    public function getMappingName()
    {
        return $this->mappingName;
    }

    /**
     * Sets the configured configuration mapping name.
     *
     * @param string $mappingName The mapping name
     */
    public function setMappingName($mappingName)
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
    public function getUploadName($obj)
    {
        if (!$this->hasNamer()) {
            return $this->getFile($obj)->getClientOriginalName();
        }

        return $this->getNamer()->name($obj, $this);
    }

    /**
     * Gets the upload directory for a given file (uses the directory namers).
     *
     * @param object $obj
     *
     * @return string The upload directory
     */
    public function getUploadDir($obj)
    {
        if (!$this->hasDirectoryNamer()) {
            return '';
        }

        $dir = $this->getDirectoryNamer()->directoryName($obj, $this);

        // strip the trailing directory separator if needed
        $dir = $dir ? rtrim($dir, '/\\') : $dir;

        return $dir;
    }

    /**
     * Gets the base upload directory.
     *
     * @return string The configured upload directory
     */
    public function getUploadDestination()
    {
        return $this->mapping['upload_destination'];
    }

    /**
     * Get uri prefix.
     *
     * @return string
     */
    public function getUriPrefix()
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
    protected function fixPropertyPath($object, $propertyPath)
    {
        if (!is_array($object)) {
            return $propertyPath;
        }

        return '[' === $propertyPath[0] ? $propertyPath : sprintf('[%s]', $propertyPath);
    }

    protected function getAccessor()
    {
        //TODO: reuse original property accessor from forms
        if (null !== $this->accessor) {
            return $this->accessor;
        }

        return $this->accessor = PropertyAccess::createPropertyAccessor();
    }
}
