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
     * @param UploadedFile $file
     * @param string       $dir
     * @param string       $name
     *
     * @return boolean
     */
    abstract protected function doUpload(UploadedFile $file, $dir, $name);

    /**
     * {@inheritDoc}
     */
    public function upload($obj)
    {
        $mappings = $this->factory->fromObject($obj);
        foreach ($mappings as $mapping) {
            $file = $mapping->getFile($obj);

            if ($file === null || !($file instanceof UploadedFile)) {
                continue;
            }

            if ($mapping->getDeleteOnUpdate() && ($name = $mapping->getFileName($obj))) {
                $dir = $mapping->getUploadDir($obj);

                $this->doRemove($dir, $name);
            }

            if ($mapping->hasNamer()) {
                $name = $mapping->getNamer()->name($obj, $mapping);
            } else {
                $name = $file->getClientOriginalName();
            }

            $dir = $mapping->getUploadDir($obj);

            $this->doUpload($file, $dir, $name);

            $mapping->setFileName($obj, $name);
        }
    }

    /**
     * Do real remove
     *
     * @param string $dir
     * @param string $name
     *
     * @return boolean
     */
    abstract protected function doRemove($dir, $name);

    /**
     * {@inheritDoc}
     */
    public function remove($obj)
    {
        $mappings = $this->factory->fromObject($obj);

        /** @var $mapping PropertyMapping */
        foreach ($mappings as $mapping) {
            if (!$mapping->getDeleteOnRemove()) {
                continue;
            }

            $name = $mapping->getFileName($obj);

            if (null === $name) {
                continue;
            }

            $dir = $mapping->getUploadDir($obj);

            $this->doRemove($dir, $name);
        }
    }

    /**
     * Do resolve path
     *
     * @param string $dir
     * @param string $name
     *
     * @return string
     */
    abstract protected function doResolvePath($dir, $name);

    /**
     * {@inheritDoc}
     */
    public function resolvePath($obj, $field, $className = null)
    {
        list($mapping, $filename) = $this->getFilename($obj, $field, $className);
        $dir = $mapping->getUploadDir($obj);

        return $this->doResolvePath($dir, $filename);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveUri($obj, $field, $className = null)
    {
        list($mapping, $filename) = $this->getFilename($obj, $field, $className);
        $uriPrefix = $mapping->getUriPrefix();

        return $filename ? ($uriPrefix . '/' . $filename) : '';
    }

    protected function getFilename($obj, $field, $className = null)
    {
        $mapping = $this->factory->fromField($obj, $field, $className);
        if (null === $mapping) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to find uploadable field named: "%s"', $field
            ));
        }

        $filename = $mapping->getFileName($obj);
        if ($filename === null) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to get filename property value: "%s"', $field
            ));
        }

        return array($mapping, $filename);
    }
}
