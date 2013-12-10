<?php

namespace Vich\UploaderBundle\Mapping;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

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

    public function __construct($filePropertyPath, $fileNamePropertyPath)
    {
        $this->filePropertyPath = $filePropertyPath;
        $this->fileNamePropertyPath = $fileNamePropertyPath;
    }

    protected function getAccessor()
    {
        if ($this->accessor !== null) {
            return $this->accessor;
        }

        return $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Gets the configured namer.
     *
     * @return null|NamerInterface The namer.
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
     * @return null|DirectoryNamerInterface The directory namer.
     */
    public function getDirectoryNamer()
    {
        return $this->directoryNamer;
    }

    /**
     * Sets the directory namer.
     *
     * @param DirectoryNamerInterface $directoryNamer The directory namer.
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
     * @param $mappingName The mapping name.
     */
    public function setMappingName($mappingName)
    {
        $this->mappingName = $mappingName;
    }

    /**
     * Gets the file property value for the given object.
     *
     * @param  object       $obj The object.
     * @return UploadedFile The file.
     */
    public function getFile($obj)
    {
        return $this->getAccessor()->getValue($obj, $this->filePropertyPath);
    }

    /**
     * Modifies the file property value for the given object.
     *
     * @param object       $obj  The object.
     * @param UploadedFile $file The new file.
     */
    public function setFile($obj, $file)
    {
        $this->getAccessor()->setValue($obj, $this->filePropertyPath, $file);
    }

    /**
     * Gets the fileName property of the given object.
     *
     * @param  object $obj The object.
     * @return string The filename.
     */
    public function getFileName($obj)
    {
        return $this->getAccessor()->getValue($obj, $this->fileNamePropertyPath);
    }

    /**
     * Modifies the fileName property of the given object.
     *
     * @param object $obj The object.
     */
    public function setFileName($obj, $value)
    {
        $this->getAccessor()->setValue($obj, $this->fileNamePropertyPath, $value);
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
     * Gets the configured upload directory.
     *
     * @param  null   $obj
     * @param  null   $field
     * @return string The configured upload directory.
     */
    public function getUploadDir($obj = null, $field = null)
    {
        if ($this->hasDirectoryNamer()) {
            return $this->getDirectoryNamer()->directoryName($obj, $field, $this->mapping['upload_destination']);
        }

        return $this->mapping['upload_destination'];
    }

    /**
     * Determines if the file should be deleted upon removal of the
     * entity.
     *
     * @return bool True if delete on remove, false otherwise.
     */
    public function getDeleteOnRemove()
    {
        return $this->mapping['delete_on_remove'];
    }

    /**
     * Determines if the file should be deleted when the file is
     * replaced by an other one.
     *
     * @return bool True if delete on update, false otherwise.
     */
    public function getDeleteOnUpdate()
    {
        return $this->mapping['delete_on_update'];
    }

    /**
     * Determines if the uploadable field should be injected with a
     * Symfony\Component\HttpFoundation\File\File instance when
     * the object is loaded from the datastore.
     *
     * @return bool True if the field should be injected, false otherwise.
     */
    public function getInjectOnLoad()
    {
        return $this->mapping['inject_on_load'];
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
}
