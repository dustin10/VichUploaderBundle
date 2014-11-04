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
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, $dir, $name)
    {
        $uploadDir = $mapping->getUploadDestination() . DIRECTORY_SEPARATOR . $dir;

        return $file->move($uploadDir, basename($name));
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
    protected function doRemove(PropertyMapping $mapping, $dir, $name)
    {
        $file = $this->doResolvePath($mapping, $dir, $name);

        return file_exists($file) ? unlink($file) : false;
    }

    /**
     * {@inheritDoc}
     */
    protected function doResolvePath(PropertyMapping $mapping, $dir, $name)
    {
        return $mapping->getUploadDestination() . DIRECTORY_SEPARATOR . $dir . $name;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveUri($obj, $mappingName, $className = null)
    {
        list($mapping, $name) = $this->getFilename($obj, $mappingName, $className);

        if (empty($name)) {
            return null;
        }

        $uriPrefix = $mapping->getUriPrefix();
        $uploadDir = $mapping->getUploadDestination() . $mapping->getUploadDir($obj);
        $parts = explode($uriPrefix, $this->convertWindowsDirectorySeparator($uploadDir));

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
