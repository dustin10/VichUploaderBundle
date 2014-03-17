<?php

namespace Vich\UploaderBundle\Storage;

use Oneup\FlysystemBundle\Filesystem\FilesystemMap;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
    protected function doUpload(UploadedFile $file, $dir, $name)
    {
        $fs = $this->getFilesystem($dir);

        $stream = fopen($file->getRealPath(), 'r+');
        $fs->writeStream($dir.'/'.$name, $stream, array(
            'content-type' => $file->getMimeType()
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function doRemove($dir, $name)
    {
        $fs = $this->getFilesystem($dir);

        $fs->delete($dir.'/'.$name);
    }

    /**
     * {@inheritDoc}
     */
    protected function doResolvePath($dir, $name)
    {
        return $dir.'/'.$name;
    }

    /**
     * Get filesystem adapter by key
     *
     * @param string $key
     *
     * @return \League\Flysystem\FilesystemInterface
     */
    protected function getFilesystem($key)
    {
        return $this->filesystemMap->get($key);
    }
}
