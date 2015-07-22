<?php

namespace Vich\UploaderBundle\Storage;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\MountManager;
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
    protected $mountManager;

    /**
     * Constructs a new instance of FlysystemStorage.
     *
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory      The factory.
     * @param League\Flysystem\MountManager                       $mountManager Gaufrete filesystem factory.
     */
    public function __construct(PropertyMappingFactory $factory, MountManager $mountManager)
    {
        parent::__construct($factory);

        $this->mountManager = $mountManager;
    }

    /**
     * {@inheritDoc}
     */
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, $dir, $name)
    {
        $fs = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        $stream = fopen($file->getRealPath(), 'r');
        $fs->writeStream($path, $stream, array(
            'mimetype' => $file->getMimeType(),
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function doRemove(PropertyMapping $mapping, $dir, $name)
    {
        $fs = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        try {
            return $fs->delete($path);
        } catch (FileNotFoundException $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doResolvePath(PropertyMapping $mapping, $dir, $name, $relative = false)
    {
        $fs = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        if ($relative) {
            return $path;
        }

        return $fs->get($path)->getPath();
    }

    /**
     * {@inheritDoc}
     */
    public function resolveStream($obj, $fieldName, $className = null)
    {
        $path = $this->resolvePath($obj, $fieldName, $className);

        if (empty($path)) {
            return;
        }

        $mapping = $this->factory->fromField($obj, $fieldName, $className);
        $fs = $this->getFilesystem($mapping);

        return $fs->readStream($path);
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
        return $this->mountManager->getFilesystem($mapping->getUploadDestination());
    }
}
