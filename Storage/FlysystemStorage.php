<?php

namespace Vich\UploaderBundle\Storage;

use League\Flysystem\FilesystemInterface;
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
     * @param PropertyMappingFactory $factory      The factory
     * @param MountManager           $mountManager Gaufrete filesystem factory
     */
    public function __construct(PropertyMappingFactory $factory, MountManager $mountManager)
    {
        parent::__construct($factory);

        $this->mountManager = $mountManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, $dir, $name)
    {
        $fs = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        $stream = fopen($file->getRealPath(), 'r');
        $fs->writeStream($path, $stream, [
            'mimetype' => $file->getMimeType(),
        ]);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * Get filesystem adapter by key.
     *
     * @param string $key
     *
     * @return FilesystemInterface
     */
    protected function getFilesystem(PropertyMapping $mapping)
    {
        return $this->mountManager->getFilesystem($mapping->getUploadDestination());
    }
}
