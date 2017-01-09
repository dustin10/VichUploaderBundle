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
     * {@inheritdoc}
     */
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, $dir, $name)
    {
        $uploadDir = $mapping->getUploadDestination().DIRECTORY_SEPARATOR.$dir;

        return $file->move($uploadDir, $name);
    }

    /**
     * {@inheritdoc}
     */
    protected function doRemove(PropertyMapping $mapping, $dir, $name)
    {
        $file = $this->doResolvePath($mapping, $dir, $name);

        return file_exists($file) ? unlink($file) : false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doResolvePath(PropertyMapping $mapping, $dir, $name, $relative = false)
    {
        $path = !empty($dir) ? $dir.DIRECTORY_SEPARATOR.$name : $name;

        if ($relative) {
            return $path;
        }

        return $mapping->getUploadDestination().DIRECTORY_SEPARATOR.$path;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveUri($obj, $mappingName, $className = null)
    {
        list($mapping, $name) = $this->getFilename($obj, $mappingName, $className);

        if (empty($name)) {
            return;
        }

        $uploadDir = $this->convertWindowsDirectorySeparator($mapping->getUploadDir($obj));
        $uploadDir = empty($uploadDir) ? '' : $uploadDir.'/';

        return sprintf('%s/%s', $mapping->getUriPrefix(), $uploadDir.$name);
    }

    private function convertWindowsDirectorySeparator($string)
    {
        return str_replace('\\', '/', $string);
    }
}
