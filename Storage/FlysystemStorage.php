<?php

namespace Vich\UploaderBundle\Storage;

use League\Flysystem\FileNotFoundException;
use Oneup\FlysystemBundle\Filesystem\FilesystemMap;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class FlysystemStorage extends AbstractStorage
{
    /**
     * @var FilesystemMap
     */
    protected $filesystemMap;

    /**
     * Constructs a new instance of FlysystemStorage.
     *
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory       The factory.
     * @param \Oneup\FlysystemBundle\Filesystem\FilesystemMap     $filesystemMap Gaufrete filesystem factory.
     */
    public function __construct(PropertyMappingFactory $factory, FilesystemMap $filesystemMap)
    {
        parent::__construct($factory);

        $this->filesystemMap = $filesystemMap;
    }

    /**
     * {@inheritDoc}
     */
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, $dir, $name)
    {
        $fs = $this->getFilesystem($mapping);
        $dir = $dir ? $dir . DIRECTORY_SEPARATOR : '';

        $stream = fopen($file->getRealPath(), 'r+');
        $fs->writeStream($dir . $name, $stream, array(
            'content-type' => $file->getMimeType()
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function doRemove(PropertyMapping $mapping, $dir, $name)
    {
        $fs = $this->getFilesystem($mapping);
        $dir = $dir ? $dir . DIRECTORY_SEPARATOR : '';

        try {
            return $fs->delete($dir . $name);
        } catch (FileNotFoundException $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doResolvePath(PropertyMapping $mapping, $dir, $name)
    {
        $fs = $this->getFilesystem($mapping);
        $dir = $dir ? $dir . DIRECTORY_SEPARATOR : '';
        $file = $fs->get($dir . $name);

        return $file->getPath();
    }

    /**
     * Get filesystem adapter by key
     *
     * @param string $key
     *
     * @return \League\Flysystem\FilesystemInterface
     */
    protected function getFilesystem(PropertyMapping $mapping)
    {
        return $this->filesystemMap->get($mapping->getUploadDestination());
    }
}
