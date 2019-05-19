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
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, ?string $dir, string $name)
    {
        $uploadDir = $mapping->getUploadDestination().\DIRECTORY_SEPARATOR.$dir;

        return $file->move($uploadDir, $name);
    }

    protected function doRemove(PropertyMapping $mapping, ?string $dir, string $name): ?bool
    {
        $file = $this->doResolvePath($mapping, $dir, $name);

        return \file_exists($file) ? \unlink($file) : false;
    }

    protected function doResolvePath(PropertyMapping $mapping, ?string $dir, string $name, ?bool $relative = false): string
    {
        $path = !empty($dir) ? $dir.\DIRECTORY_SEPARATOR.$name : $name;

        if ($relative) {
            return $path;
        }

        return $mapping->getUploadDestination().\DIRECTORY_SEPARATOR.$path;
    }

    public function resolveUri($obj, string $mappingName, string $className = null): ?string
    {
        [$mapping, $name] = $this->getFilename($obj, $mappingName, $className);

        if (empty($name)) {
            return null;
        }

        $uploadDir = $this->convertWindowsDirectorySeparator($mapping->getUploadDir($obj));
        $uploadDir = empty($uploadDir) ? '' : $uploadDir.'/';

        return \sprintf('%s/%s', $mapping->getUriPrefix(), $uploadDir.$name);
    }

    private function convertWindowsDirectorySeparator(string $string): string
    {
        return \str_replace('\\', '/', $string);
    }
}
