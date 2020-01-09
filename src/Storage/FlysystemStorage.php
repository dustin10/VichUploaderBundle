<?php

namespace Vich\UploaderBundle\Storage;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class FlysystemStorage extends AbstractStorage
{
    /**
     * @var MountManager|ContainerInterface a registry to get FilesystemInterface instances
     */
    protected $registry;

    public function __construct(PropertyMappingFactory $factory, $registry)
    {
        parent::__construct($factory);

        if (!$registry instanceof MountManager && !$registry instanceof ContainerInterface) {
            throw new \TypeError(\sprintf('Argument 2 passed to %s::__construct() must be an instance of %s or an instance of %s, %s given.', __CLASS__, MountManager::class, ContainerInterface::class, \is_object($registry) ? \get_class($registry) : \gettype($registry)));
        }

        $this->registry = $registry;
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

        return (string) $fs->getAdapter()->applyPathPrefix($path);
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
        if ($this->registry instanceof MountManager) {
            return $this->registry->getFilesystem($mapping->getUploadDestination());
        }

        return $this->registry->get($mapping->getUploadDestination());
    }
}
