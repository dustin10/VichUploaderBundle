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
class FileSystemStorage extends AbstractStorage
{
    /**
     * {@inheritDoc}
     */
    protected function doUpload(UploadedFile $file, $dir, $name)
    {
        $uploadDir = $this->getUploadDirectory($dir, $name);
        $fileName = basename($name);
        return $file->move($uploadDir, $fileName);
    }

    /**
     * Do real remove
     *
     * @param string $dir
     * @param string $name
     *
     * @internal param object $obj
     * @return boolean
     */
    protected function doRemove($dir, $name)
    {
        $file = $dir . DIRECTORY_SEPARATOR . $name;
        return file_exists($file) ? unlink($file) : false;
    }

    /**
     * {@inheritDoc}
     */
    protected function doResolvePath($dir, $name)
    {
        return $dir . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveUri($obj, $field)
    {
        $mapping = $this->factory->fromField($obj, $field);
        if (null === $mapping) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to find uploadable field named: "%s"',
                $field
            ));
        }

        $name = $mapping->getFileNameProperty()->getValue($obj);
        if ($name === null) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to get filename property value: "%s"',
                $field
            ));
        }

        $uriPrefix = $mapping->getUriPrefix();
        $parts = explode($uriPrefix, $this->convertWindowsDirectorySeparator($mapping->getUploadDir($obj, $field)));
        return sprintf('%s/%s', $uriPrefix . array_pop($parts), $name);
    }

    /**
     * @param $string
     * @return string
     */
    protected function convertWindowsDirectorySeparator($string)
    {
        return str_replace('\\', '/', $string);
    }

    /**
     * Returns the upload directory
     *
     * The method extract any directory present in $name and combine
     * it with $dir to get the right upload directory.
     *
     * @param $dir
     * @param $name
     * @return string
     */
    protected function getUploadDirectory($dir, $name)
    {
        return rtrim(
            str_replace(
                DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                $dir . DIRECTORY_SEPARATOR . dirname($name)
            ),
            DIRECTORY_SEPARATOR . '.'
        );
    }
}
