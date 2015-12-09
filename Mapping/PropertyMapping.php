<?php

namespace Vich\UploaderBundle\Mapping;

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
     * @var NamerInterface $namer
     */
    protected $namer;

    /**
     * @var DirectoryNamerInterface $directoryNamer
     */
    protected $directoryNamer;

    /**
     * @var array $mapping
     */
    protected $mapping;

    /**
     * @var string $mappingName
     */
    protected $mappingName;

    /**
     * @var string $filePropertyPath
     */
    protected $filePropertyPath;

    /**
     * @var string $fileNamePropertyPath
     */
    protected $fileNamePropertyPath;

    /**
     * @var PropertyAccess $accessor
     */
    protected $accessor;

    /**
     * @param string $filePropertyPath     The path to the "file" property.
     * @param string $fileNamePropertyPath The path to the "filename" property.
     */
    public function __construct($filePropertyPath, $fileNamePropertyPath)
    {
        $this->filePropertyPath = $filePropertyPath;
        $this->fileNamePropertyPath = $fileNamePropertyPath;
    }

    /**
     * Gets the file property value for the given object.
     *
     * @param  object                                             $obj The object.
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile The file.
     */
    public function getFile($obj)
    {
        $propertyPath = $this->fixPropertyPath($obj, $this->filePropertyPath);

        return $this->getAccessor()->getValue($obj, $propertyPath);
    }

    /**
     * Modifies the file property value for the given object.
     *
     * @param object                                             $obj  The object.
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file The new file.
     */
    public function setFile($obj, $file)
    {
        $propertyPath = $this->fixPropertyPath($obj, $this->filePropertyPath);
        $this->getAccessor()->setValue($obj, $propertyPath, $file);
    }

    /**
     * Gets the fileName property of the given object.
     *
     * @param object $obj The object.
     *
     * @return string The filename.
     */
    public function getFileName($obj)
    {
        $propertyPath = $this->fixPropertyPath($obj, $this->fileNamePropertyPath);

        return $this->getAccessor()->getValue($obj, $propertyPath);
    }

    /**
     * Modifies the fileName property of the given object.
     *
     * @param object $obj The object.
     * @param $value
     */
    public function setFileName($obj, $value)
    {
        $propertyPath = $this->fixPropertyPath($obj, $this->fileNamePropertyPath);
        $this->getAccessor()->setValue($obj, $propertyPath, $value);
    }

    /**
     * Gets the configured file property name.
     *
     * @return string The name.
     */
    public function getFilePropertyName()
    {
        return $this->filePropertyPath;
    }

    /**
     * Gets the configured filename property name.
     *
     * @return string The name.
     */
    public function getFileNamePropertyName()
    {
        return $this->fileNamePropertyPath;
    }

    /**
     * Gets the configured namer.
     *
     * @return null|\Vich\UploaderBundle\Naming\NamerInterface The namer.
     */
    public function getNamer()
    {
        return $this->namer;
    }

    /**
     * Sets the namer.
     *
     * @param \Vich\UploaderBundle\Naming\NamerInterface $namer The namer.
     */
    public function setNamer(NamerInterface $namer)
    {
        $this->namer = $namer;
    }

    /**
     * Determines if the mapping has a custom namer configured.
     *
     * @return bool True if has namer, false otherwise.
     */
    public function hasNamer()
    {
        return null !== $this->namer;
    }

    /**
     * Gets the configured directory namer.
     *
     * @return null|\Vich\UploaderBundle\Naming\DirectoryNamerInterface The directory namer.
     */
    public function getDirectoryNamer()
    {
        return $this->directoryNamer;
    }

    /**
     * Sets the directory namer.
     *
     * @param \Vich\UploaderBundle\Naming\DirectoryNamerInterface $directoryNamer The directory namer.
     */
    public function setDirectoryNamer(DirectoryNamerInterface $directoryNamer)
    {
        $this->directoryNamer = $directoryNamer;
    }

    /**
     * Determines if the mapping has a custom directory namer configured.
     *
     * @return bool True if has directory namer, false otherwise.
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
     * @return string The mapping name.
     */
    public function getMappingName()
    {
        return $this->mappingName;
    }

    /**
     * Sets the configured configuration mapping name.
     *
     * @param string $mappingName The mapping name.
     */
    public function setMappingName($mappingName)
    {
        $this->mappingName = $mappingName;
    }

    /**
     * Gets the upload directory for a given file (uses the directory namers).
     *
     * @param object $obj
     *
     * @return string The upload directory.
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
     * @return string The configured upload directory.
     */
    public function getUploadDestination()
    {
        return $this->mapping['upload_destination'];
    }

    /**
     * Get uri prefix
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
     * @param object|array $object       The object to inspect.
     * @param string       $propertyPath The property path to fix.
     *
     * @return string The fixed property path.
     */
    protected function fixPropertyPath($object, $propertyPath)
    {
        if (!is_array($object)) {
            return $propertyPath;
        }

        return $propertyPath[0] === '[' ? $propertyPath : sprintf('[%s]', $propertyPath);
    }

    protected function getAccessor()
    {
        if ($this->accessor !== null) {
            return $this->accessor;
        }

        return $this->accessor = PropertyAccess::createPropertyAccessor();
    }
}
