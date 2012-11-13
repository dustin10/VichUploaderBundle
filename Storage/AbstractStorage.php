<?php
namespace Vich\UploaderBundle\Storage;

use Vich\UploaderBundle\Storage\StorageInterface;
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
            $file = $mapping->getPropertyValue($obj);
            if (is_null($file) || !($file instanceof UploadedFile)) {
                continue;
            }

            if ($mapping->getDeleteOnUpdate() && $mapping->getFileNameProperty()->getValue($obj)) {
                $name = $mapping->getFileNameProperty()->getValue($obj);

                $dir = $mapping->getUploadDir($obj, $mapping->getProperty()->getName());

                $this->doRemove($dir, $name);
            }

            if ($mapping->hasNamer()) {
                $name = $mapping->getNamer()->name($obj, $mapping->getProperty()->getName());
            } else {
                $name = $file->getClientOriginalName();
            }

            $dir = $mapping->getUploadDir($obj, $mapping->getProperty()->getName());

            $this->doUpload($file, $dir, $name);

            $mapping->getFileNameProperty()->setValue($obj, $name);
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
            if ($mapping->getDeleteOnRemove()) {
                $name = $mapping->getFileNameProperty()->getValue($obj);
                if (null === $name) {
                    continue;
                }

                $dir = $mapping->getUploadDir($obj, $mapping->getProperty()->getName());

                $this->doRemove($dir, $name);
            }
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
    public function resolvePath($obj, $field)
    {
        $mapping = $this->factory->fromField($obj, $field);
        if (null === $mapping) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to find uploadable field named: "%s"', $field
            ));
        }
        $name = $mapping->getFileNameProperty()->getValue($obj);
        if ($name === null) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to get filename property value: "%s"', $field
            ));
        }

        $dir = $mapping->getUploadDir($obj, $field);

        return $this->doResolvePath($dir, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveUri($obj, $field)
    {
        $mapping = $this->factory->fromField($obj, $field);
        if (null === $mapping) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to find uploadable field named: "%s"', $field
            ));
        }

        $name = $mapping->getFileNameProperty()->getValue($obj);
        if ($name === null) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to get filename property value: "%s"', $field
            ));
        }

        $uriPrefix = $mapping->getUriPrefix();

        return $name ? ($uriPrefix . '/' . $name) : '';
    }
}
