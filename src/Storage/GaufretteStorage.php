<?php

namespace Vich\UploaderBundle\Storage;

use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\FilesystemInterface;
use Gaufrette\FilesystemMapInterface;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\PropertyMappingFactoryInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;

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
     * @param FilesystemMapInterface          $filesystemMap Gaufrette filesystem factory
     * @param string                          $protocol      Gaufrette stream wrapper protocol
     */
    public function __construct(PropertyMappingFactoryInterface $factory, protected FilesystemMapInterface $filesystemMap, protected string $protocol = 'gaufrette')
    {
        parent::__construct($factory);
    }

    protected function doUpload(PropertyMappingInterface $mapping, File $file, ?string $dir, string $name): void
    {
        $filesystem = $this->getFilesystem($mapping);
        $path = (\is_string($dir) && '' !== $dir) ? $dir.'/'.$name : $name;

        $content = \file_get_contents($file->getPathname());
        if (false === $content) {
            throw new \RuntimeException(\sprintf('Could not read file "%s"', $file->getPathname()));
        }

        $filesystem->write($path, $content, true);

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

    public function listFiles(PropertyMappingInterface $mapping): iterable
    {
        $filesystem = $this->getFilesystem($mapping);

        try {
            $keys = $filesystem->listKeys();

            foreach ($keys['keys'] ?? [] as $key) {
                if ($filesystem->has($key) && !$this->isDirectory($filesystem, $key)) {
                    // Try to get the last modified timestamp
                    $lastModifiedAt = null;
                    try {
                        $lm = $filesystem->mtime($key);
                        if (null !== $lm) {
                            $lastModifiedAt = (int) $lm;
                        }
                    } catch (\Exception) {
                        // Timestamp not available for this adapter
                    }

                    yield new StoredFile($key, $lastModifiedAt);
                }
            }
        } catch (\Exception) {
            // If filesystem doesn't exist or can't be read, return empty
            return;
        }
    }

    private function isDirectory(FilesystemInterface $filesystem, string $key): bool
    {
        // Gaufrette doesn't have a native isDirectory method
        // Try to detect if it's a directory by checking if it ends with /
        // or if we can list keys with this prefix
        if (\str_ends_with($key, '/')) {
            return true;
        }

        // If we can get the file and it has size 0 and is not readable, it might be a directory
        try {
            $filesystem->get($key);

            return false;
        } catch (\Exception) {
            return true;
        }
    }
}
