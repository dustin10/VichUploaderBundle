<?php

namespace Vich\UploaderBundle\Storage;

use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\Exception\FileNotFound;
use Gaufrette\File;
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
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory       The factory.
     * @param \Knp\Bundle\GaufretteBundle\FilesystemMap           $filesystemMap Gaufrete filesystem factory.
     * @param string                                              $protocol      Gaufrette stream wrapper protocol.
     */
    public function __construct(PropertyMappingFactory $factory, FilesystemMap $filesystemMap, $protocol = 'gaufrette')
    {
        parent::__construct($factory);

        $this->filesystemMap = $filesystemMap;
        $this->protocol      = $protocol;
    }

    /**
     * {@inheritDoc}
     */
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, $dir, $name)
    {
        $filesystem = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        if ($filesystem->getAdapter() instanceof MetadataSupporter) {
            $gaufretteFile = new File($path, $filesystem);
            $gaufretteFile->setContent(file_get_contents($file->getPathname()), array('contentType' => $file->getMimeType()));
        } else {
            $filesystem->write($path, file_get_contents($file->getPathname()), true);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doRemove(PropertyMapping $mapping, $dir, $name)
    {
        $filesystem = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        try {
            return $filesystem->delete($path);
        } catch (FileNotFound $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doResolvePath(PropertyMapping $mapping, $dir, $name, $relative = false)
    {
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        if ($relative) {
            return $path;
        }

        return $this->protocol.'://'.$mapping->getUploadDestination().'/'.$path;
    }

    /**
     * Get filesystem adapter from the property mapping
     *
     * @param PropertyMapping $mapping
     *
     * @return \Gaufrette\Filesystem
     */
    protected function getFilesystem(PropertyMapping $mapping)
    {
        return $this->filesystemMap->get($mapping->getUploadDestination());
    }
}
