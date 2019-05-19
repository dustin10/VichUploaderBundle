<?php

namespace Vich\UploaderBundle\Storage;

use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\Exception\FileNotFound;
use Gaufrette\Filesystem;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * GaufretteStorage.
 *
 * @author Stefan Zerkalica <zerkalica@gmail.com>
 */
class GaufretteStorage extends AbstractStorage
{
    /**
     * @var FilesystemMap
     */
    protected $filesystemMap;

    /**
     * @var string
     */
    protected $protocol;

    /**
     * Constructs a new instance of FileSystemStorage.
     *
     * @param PropertyMappingFactory $factory       The factory
     * @param FilesystemMap          $filesystemMap Gaufrete filesystem factory
     * @param string                 $protocol      Gaufrette stream wrapper protocol
     */
    public function __construct(PropertyMappingFactory $factory, FilesystemMap $filesystemMap, string $protocol = 'gaufrette')
    {
        parent::__construct($factory);

        $this->filesystemMap = $filesystemMap;
        $this->protocol = $protocol;
    }

    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, ?string $dir, string $name): void
    {
        $filesystem = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        if ($filesystem->getAdapter() instanceof MetadataSupporter) {
            $filesystem->getAdapter()->setMetadata($path, ['contentType' => $file->getMimeType()]);
        }

        $filesystem->write($path, \file_get_contents($file->getPathname()), true);
    }

    protected function doRemove(PropertyMapping $mapping, ?string $dir, string $name): ?bool
    {
        $filesystem = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        try {
            return $filesystem->delete($path);
        } catch (FileNotFound $e) {
            return false;
        }
    }

    protected function doResolvePath(PropertyMapping $mapping, ?string $dir, string $name, ?bool $relative = false): string
    {
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        if ($relative) {
            return $path;
        }

        return $this->protocol.'://'.$mapping->getUploadDestination().'/'.$path;
    }

    /**
     * Get filesystem adapter from the property mapping.
     *
     * @param PropertyMapping $mapping
     *
     * @return Filesystem
     */
    protected function getFilesystem(PropertyMapping $mapping): Filesystem
    {
        return $this->filesystemMap->get($mapping->getUploadDestination());
    }
}
