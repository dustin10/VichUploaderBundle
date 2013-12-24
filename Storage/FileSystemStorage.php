<?php

namespace Vich\UploaderBundle\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Vich\UploaderBundle\Mapping\PropertyMapping;

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
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, $destinationPath)
    {
        $uploadDir = $this->getUploadDirectory($mapping->getUploadDestination(), $destinationPath);
        $fileName = basename($destinationPath);

        return $file->move($uploadDir, $fileName);
    }

    /**
     * {@inheritDoc}
     */
    protected function doRemove(PropertyMapping $mapping, $path)
    {
        $file = $mapping->getUploadDestination() . DIRECTORY_SEPARATOR . $path;

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
    public function resolveUri($obj, $field, $className = null)
    {
        list($mapping, $name) = $this->getFileName($obj, $field, $className);
        $uriPrefix = $mapping->getUriPrefix();
        $parts = explode($uriPrefix, $this->convertWindowsDirectorySeparator($mapping->getUploadDestination()));

        return sprintf('%s/%s', $uriPrefix . array_pop($parts), $this->convertWindowsDirectorySeparator($name));
    }

    /**
     * @param $string
     *
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
     *
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
