<?php

namespace Vich\UploaderBundle\Storage;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * FileSystemStorage.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class FileSystemStorage extends AbstractStorage
{
    protected function doUpload(PropertyMapping $mapping, File $file, ?string $dir, string $name): ?File
    {
        $uploadDir = $mapping->getUploadDestination().\DIRECTORY_SEPARATOR.$dir;

        if (!\file_exists($uploadDir)) {
            if (!\mkdir($uploadDir, recursive: true)) {
                throw new \Exception('Could not create directory "'.$uploadDir.'"');
            }
        }
        if (!\is_dir($uploadDir)) {
            throw new \Exception('Tried to move file to directory "'.$uploadDir.'" but it is a file');
        }

        if ($file instanceof UploadedFile) {
            return $file->move($uploadDir, $name);
        }
        $targetPathname = $uploadDir.\DIRECTORY_SEPARATOR.$name;
        if (!\copy($file->getPathname(), $targetPathname)) {
            throw new \RuntimeException('Could not copy file');
        }

        return new File($targetPathname);
    }

    protected function doRemove(PropertyMapping $mapping, ?string $dir, string $name): ?bool
    {
        $file = $this->doResolvePath($mapping, $dir, $name);

        return \file_exists($file) && \unlink($file);
    }

    protected function doResolvePath(PropertyMapping $mapping, ?string $dir, string $name, ?bool $relative = false): string
    {
        $path = !empty($dir) ? $dir.\DIRECTORY_SEPARATOR.$name : $name;

        if ($relative) {
            return $path;
        }

        return $mapping->getUploadDestination().\DIRECTORY_SEPARATOR.$path;
    }

    public function resolveUri(object|array $obj, ?string $fieldName = null, ?string $className = null): ?string
    {
        [$mapping, $name] = $this->getFilename($obj, $fieldName, $className);

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
