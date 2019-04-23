<?php

namespace Vich\UploaderBundle\Storage;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
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
     * @var MountManager Flysystem mount manager
     */
    protected $mountManager;

    public function __construct(PropertyMappingFactory $factory, MountManager $mountManager)
    {
        parent::__construct($factory);

        $this->mountManager = $mountManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, ?string $dir, string $name): void
    {
        $fs = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        $stream = \fopen($file->getRealPath(), 'rb');
        $fs->putStream($path, $stream, [
            'mimetype' => $file->getMimeType(),
        ]);
    }

    protected function doRemove(PropertyMapping $mapping, ?string $dir, string $name): ?bool
    {
        $fs = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        try {
            return $fs->delete($path);
        } catch (FileNotFoundException $e) {
            return false;
        }
    }

    protected function doResolvePath(PropertyMapping $mapping, ?string $dir, string $name, ?bool $relative = false): string
    {
        $fs = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        if ($relative) {
            return $path;
        }

        return (string) $fs->get($path)->getPath();
    }

    public function resolveStream($obj, string $fieldName, ?string $className = null)
    {
        $path = $this->resolvePath($obj, $fieldName, $className, true);

        if (empty($path)) {
            return null;
        }

        $mapping = $this->factory->fromField($obj, $fieldName, $className);
        $fs = $this->getFilesystem($mapping);

        return $fs->readStream($path);
    }

    protected function getFilesystem(PropertyMapping $mapping): FilesystemInterface
    {
        return $this->mountManager->getFilesystem($mapping->getUploadDestination());
    }
}
