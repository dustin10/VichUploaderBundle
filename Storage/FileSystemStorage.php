<?php

namespace Vich\UploaderBundle\Storage;

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
    public function resolveUri($obj, $field, $className = null)
    {
        list($mapping, $name) = $this->getFilename($obj, $field, $className);
        $uriPrefix = $mapping->getUriPrefix();
        $parts = explode($uriPrefix, $this->convertWindowsDirectorySeparator($mapping->getUploadDir($obj)));

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
