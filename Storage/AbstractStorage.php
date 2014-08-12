<?php

namespace Vich\UploaderBundle\Storage;

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
     * @var \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory
     */
    protected $factory;

    /**
     * Constructs a new instance of FileSystemStorage.
     *
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory The factory.
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

        // determine the file's directory
        $dir = $mapping->getUploadDir($obj);

        // determine the file's name
        if ($mapping->hasNamer()) {
            $name = $mapping->getNamer()->name($obj, $mapping);
        } else {
            $name = $file->getClientOriginalName();
        }

        $mapping->setFileName($obj, $name);

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
            return;
        }

        $this->doRemove($mapping, $mapping->getUploadDir($obj), $name);
    }

    /**
     * Do resolve path
     *
     * @param PropertyMapping $mapping
     * @param string          $dir
     * @param string          $name
     *
     * @return string
     */
    abstract protected function doResolvePath(PropertyMapping $mapping, $dir, $name);

    /**
     * {@inheritDoc}
     */
    public function resolvePath($obj, $mappingName, $className = null)
    {
        list($mapping, $filename) = $this->getFilename($obj, $mappingName, $className);
        $dir = $mapping->getUploadDir($obj);

        return $this->doResolvePath($mapping, $dir, $filename);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveUri($obj, $mapping, $className = null)
    {
        list($mapping, $filename) = $this->getFilename($obj, $mapping, $className);

        if (!$filename) {
            return '';
        }

        return $mapping->getUriPrefix() . '/' . $mapping->getUploadDir($obj) . $filename;
    }

    /**
     * @note extension point.
     */
    protected function getFilename($obj, $mappingName, $className = null)
    {
        $mapping = $mappingName instanceof PropertyMapping ? $mappingName : $this->factory->fromName($obj, $mappingName, $className);
        $filename = $mapping->getFileName($obj);

        if ($filename === null) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to get filename value for mapping: "%s" (check that the mapping name is correct and that there is an uploaded file)', $mapping->getMappingName()
            ));
        }

        return array($mapping, $filename);
    }
}
