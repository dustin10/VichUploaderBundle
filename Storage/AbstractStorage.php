<?php

namespace Vich\UploaderBundle\Storage;

use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FileSystemStorage.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var PropertyMappingFactory $factory
     */
    protected $factory;

    /**
     * Constructs a new instance of FileSystemStorage.
     *
     * @param PropertyMappingFactory $factory The factory.
     */
    public function __construct(PropertyMappingFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Do real upload
     *
     * @param PropertyMapping $mapping
     * @param UploadedFile    $file
     * @param string          $dir
     * @param string          $name
     *
     * @return boolean
     */
    abstract protected function doUpload(PropertyMapping $mapping, UploadedFile $file, $dir, $name);

    /**
     * {@inheritDoc}
     */
    public function upload($obj, PropertyMapping $mapping)
    {
        $file = $mapping->getFile($obj);

        if ($file === null || !($file instanceof UploadedFile)) {
            throw new \LogicException('No uploadable file found');
        }

        // determine the file's name
        if ($mapping->hasNamer()) {
            $name = $mapping->getNamer()->name($obj, $mapping);
        } else {
            $name = $file->getClientOriginalName();
        }

        $mapping->setFileName($obj, $name);

        // determine the file's directory
        $dir = $mapping->getUploadDir($obj);

        $this->doUpload($mapping, $file, $dir, $name);
    }

    /**
     * Do real remove
     *
     * @param PropertyMapping $mapping
     * @param string          $dir
     * @param string          $name
     *
     * @return boolean
     */
    abstract protected function doRemove(PropertyMapping $mapping, $dir, $name);

    /**
     * {@inheritDoc}
     */
    public function remove($obj, PropertyMapping $mapping)
    {
        $name = $mapping->getFileName($obj);

        // the non-strict comparison is done on purpose: we want to skip
        // null and empty filenames
        if (null == $name) {
            return false;
        }

        return $this->doRemove($mapping, $mapping->getUploadDir($obj), $name);
    }

    /**
     * Do resolve path
     *
     * @param PropertyMapping $mapping  The mapping representing the field.
     * @param string          $dir      The directory in which the file is uploaded.
     * @param string          $name     The file name.
     * @param bool            $relative Whether the path should be relative or absolute.
     *
     * @return string
     */
    abstract protected function doResolvePath(PropertyMapping $mapping, $dir, $name, $relative = false);

    /**
     * {@inheritDoc}
     */
    public function resolvePath($obj, $fieldName, $className = null, $relative = false)
    {
        list($mapping, $filename) = $this->getFilename($obj, $fieldName, $className);

        if (empty($filename)) {
            return null;
        }

        return $this->doResolvePath($mapping, $mapping->getUploadDir($obj), $filename, $relative);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveUri($obj, $fieldName, $className = null)
    {
        list($mapping, $filename) = $this->getFilename($obj, $fieldName, $className);

        if (empty($filename)) {
            return null;
        }

        $dir = $mapping->getUploadDir($obj);
        $path = !empty($dir) ? $dir.'/'.$filename : $filename;

        return $mapping->getUriPrefix().'/'.$path;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveStream($obj, $fieldName, $className = null)
    {
        $path = $this->resolvePath($obj, $fieldName, $className);

        if (empty($path)) {
            return null;
        }

        return fopen($path, 'rb');
    }

    /**
     *  note: extension point.
     */
    protected function getFilename($obj, $fieldName, $className = null)
    {
        $mapping = $this->factory->fromField($obj, $fieldName, $className);

        if ($mapping === null) {
            throw new MappingNotFoundException(sprintf('Mapping not found for field "%s"', $fieldName));
        }

        return array($mapping, $mapping->getFileName($obj));
    }
}
