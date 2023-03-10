<?php

namespace Vich\UploaderBundle\Storage;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * FileSystemStorage.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
abstract class AbstractStorage implements StorageInterface
{
    public function __construct(protected readonly PropertyMappingFactory $factory)
    {
    }

    /**
     * @return mixed|void
     */
    abstract protected function doUpload(PropertyMapping $mapping, File $file, ?string $dir, string $name);

    public function upload(object $obj, PropertyMapping $mapping): void
    {
        $file = $mapping->getFile($obj);
        if (!$file instanceof UploadedFile && !$file instanceof ReplacingFile) {
            throw new \LogicException('No uploadable file found');
        }

        $name = $mapping->getUploadName($obj);
        $mapping->setFileName($obj, $name);
        $mimeType = $file->getMimeType();

        $mapping->writeProperty($obj, 'size', $file->getSize());
        $mapping->writeProperty($obj, 'mimeType', $mimeType);
        $mapping->writeProperty($obj, 'originalName', $file->getClientOriginalName());

        if (null !== $mimeType && \str_contains($mimeType, 'image/') && 'image/svg+xml' !== $mimeType && false !== $dimensions = @\getimagesize($file)) {
            $mapping->writeProperty($obj, 'dimensions', \array_splice($dimensions, 0, 2));
        }

        $dir = $mapping->getUploadDir($obj);

        $this->doUpload($mapping, $file, $dir, $name);
    }

    abstract protected function doRemove(PropertyMapping $mapping, ?string $dir, string $name): ?bool;

    public function remove(object $obj, PropertyMapping $mapping): ?bool
    {
        $name = $mapping->getFileName($obj);

        if (empty($name)) {
            return false;
        }

        return $this->doRemove($mapping, $mapping->getUploadDir($obj), $name);
    }

    /**
     * Do resolve path.
     *
     * @param PropertyMapping $mapping  The mapping representing the field
     * @param string|null     $dir      The directory in which the file is uploaded
     * @param string          $name     The file name
     * @param bool            $relative Whether the path should be relative or absolute
     */
    abstract protected function doResolvePath(PropertyMapping $mapping, ?string $dir, string $name, ?bool $relative = false): string;

    public function resolvePath(object|array $obj, ?string $fieldName = null, ?string $className = null, ?bool $relative = false): ?string
    {
        [$mapping, $filename] = $this->getFilename($obj, $fieldName, $className);

        if (empty($filename)) {
            return null;
        }

        return $this->doResolvePath($mapping, $mapping->getUploadDir($obj), $filename, $relative);
    }

    public function resolveUri(object|array $obj, ?string $fieldName = null, ?string $className = null): ?string
    {
        [$mapping, $filename] = $this->getFilename($obj, $fieldName, $className);

        if (empty($filename)) {
            return null;
        }

        $dir = $mapping->getUploadDir($obj);
        $path = !empty($dir) ? $dir.'/'.$filename : $filename;

        return $mapping->getUriPrefix().'/'.$path;
    }

    public function resolveStream(object|array $obj, ?string $fieldName = null, ?string $className = null)
    {
        $path = $this->resolvePath($obj, $fieldName, $className);

        if (empty($path)) {
            return null;
        }

        return \fopen($path, 'rb');
    }

    /**
     * note: extension point.
     *
     * @throws MappingNotFoundException
     * @throws \RuntimeException
     * @throws \Vich\UploaderBundle\Exception\NotUploadableException
     */
    protected function getFilename(object|array $obj, ?string $fieldName = null, ?string $className = null): array
    {
        $mapping = null === $fieldName ?
            $this->factory->fromFirstField($obj, $className) :
            $this->factory->fromField($obj, $fieldName, $className)
        ;

        if (null === $mapping) {
            throw new MappingNotFoundException(\sprintf('Mapping not found for field "%s"', $fieldName));
        }

        return [$mapping, $mapping->getFileName($obj)];
    }
}
