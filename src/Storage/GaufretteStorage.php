<?php

namespace Vich\UploaderBundle\Storage;

use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\FilesystemInterface;
use Gaufrette\FilesystemMapInterface;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactoryInterface;

/**
 * GaufretteStorage.
 *
 * @author Stefan Zerkalica <zerkalica@gmail.com>
 */
final class GaufretteStorage extends AbstractStorage
{
    /**
     * Constructs a new instance of FileSystemStorage.
     *
     * @param PropertyMappingFactoryInterface $factory       The factory
     * @param FilesystemMapInterface $filesystemMap Gaufrette filesystem factory
     * @param string                 $protocol      Gaufrette stream wrapper protocol
     */
    public function __construct(PropertyMappingFactoryInterface $factory, protected FilesystemMapInterface $filesystemMap, protected string $protocol = 'gaufrette')
    {
        parent::__construct($factory);
    }

    protected function doUpload(PropertyMappingInterface $mapping, File $file, ?string $dir, string $name): void
    {
        $filesystem = $this->getFilesystem($mapping);
        $path = (\is_string($dir) && '' !== $dir) ? $dir.'/'.$name : $name;

        $filesystem->write($path, \file_get_contents($file->getPathname()), true);

        if ($filesystem->getAdapter() instanceof MetadataSupporter) {
            $filesystem->getAdapter()->setMetadata($path, ['contentType' => $file->getMimeType()]);
        }
    }

    protected function doRemove(PropertyMappingInterface $mapping, ?string $dir, string $name): ?bool
    {
        $filesystem = $this->getFilesystem($mapping);
        $path = (\is_string($dir) && '' !== $dir) ? $dir.'/'.$name : $name;

        return $filesystem->delete($path);
    }

    protected function doResolvePath(PropertyMappingInterface $mapping, ?string $dir, string $name, ?bool $relative = false): string
    {
        $path = (\is_string($dir) && '' !== $dir) ? $dir.'/'.$name : $name;

        if ($relative) {
            return $path;
        }

        return $this->protocol.'://'.$mapping->getUploadDestination().'/'.$path;
    }

    /**
     * Get filesystem adapter from the property mapping.
     */
    protected function getFilesystem(PropertyMappingInterface $mapping): FilesystemInterface
    {
        return $this->filesystemMap->get($mapping->getUploadDestination());
    }
}
