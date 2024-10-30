<?php

namespace Vich\UploaderBundle\Storage;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\ErrorHandler\Error\UndefinedMethodError;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
final class FlysystemStorage extends AbstractStorage
{
    /**
     * @var MountManager|ContainerInterface a registry to get FilesystemInterface instances
     */
    protected MountManager|ContainerInterface $registry;

    /**
     * @var bool use flysystem to resolve the uri
     */
    protected bool $useFlysystemToResolveUri;

    /**
     * @param MountManager|ContainerInterface|mixed $registry
     */
    public function __construct(PropertyMappingFactory $factory, $registry, bool $useFlysystemToResolveUri = false)
    {
        parent::__construct($factory);

        if (!$registry instanceof MountManager && !$registry instanceof ContainerInterface) {
            throw new \TypeError(\sprintf('Argument 2 passed to %s::__construct() must be an instance of %s or an instance of %s, %s given.', self::class, MountManager::class, ContainerInterface::class, \get_debug_type($registry)));
        }

        $this->registry = $registry;
        $this->useFlysystemToResolveUri = $useFlysystemToResolveUri;
    }

    protected function doUpload(PropertyMapping $mapping, File $file, ?string $dir, string $name): void
    {
        $fs = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        $stream = \fopen($file->getRealPath(), 'rb');
        try {
            $fs->writeStream($path, $stream, [
                'mimetype' => $file->getMimeType(),
            ]);
        } catch (FilesystemException $e) {
            throw new CannotWriteFileException($e->getMessage());
        }
    }

    protected function doRemove(PropertyMapping $mapping, ?string $dir, string $name): ?bool
    {
        $fs = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        $fs->delete($path);

        return true;
    }

    protected function doResolvePath(PropertyMapping $mapping, ?string $dir, string $name, ?bool $relative = false): string
    {
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        if ($relative) {
            return $path;
        }

        return $path;
    }

    public function resolveUri(object|array $obj, ?string $fieldName = null, ?string $className = null): ?string
    {
        if (!$this->useFlysystemToResolveUri) {
            return parent::resolveUri($obj, $fieldName, $className);
        }

        $path = $this->resolvePath($obj, $fieldName, $className, true);

        if (empty($path)) {
            return null;
        }

        $mapping = null === $fieldName ?
            $this->factory->fromFirstField($obj, $className) :
            $this->factory->fromField($obj, $fieldName, $className);
        $fs = $this->getFilesystem($mapping);

        try {
            return $fs->publicUrl($path);
        } catch (FilesystemException|UndefinedMethodError) {
            return $mapping->getUriPrefix().'/'.$path;
        }
    }

    public function resolveStream(object|array $obj, ?string $fieldName = null, ?string $className = null)
    {
        $path = $this->resolvePath($obj, $fieldName, $className, true);

        if (empty($path)) {
            return null;
        }

        $mapping = null === $fieldName ?
            $this->factory->fromFirstField($obj, $className) :
            $this->factory->fromField($obj, $fieldName, $className);
        $fs = $this->getFilesystem($mapping);

        try {
            return $fs->readStream($path);
        } catch (FilesystemException) {
            return null;
        }
    }

    protected function getFilesystem(PropertyMapping $mapping): FilesystemOperator
    {
        if ($this->registry instanceof MountManager) {
            return $this->registry;
        }

        return $this->registry->get($mapping->getUploadDestination());
    }
}
