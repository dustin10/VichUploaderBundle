<?php

namespace Vich\UploaderBundle\Metadata;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final class CacheWarmer implements CacheWarmerInterface
{
    /** @var string */
    private $dir;

    /** @var MetadataReader */
    private $metadataReader;

    public function __construct(string $dir, MetadataReader $metadataReader)
    {
        $this->dir = $dir;
        $this->metadataReader = $metadataReader;
    }

    /**
     * @param string $cacheDir
     */
    public function warmUp($cacheDir): array
    {
        if (empty($this->dir)) {
            return [];
        }
        $files = [];
        if (!\is_dir($this->dir)) {
            if (!\mkdir($concurrentDirectory = $this->dir, 0777, true) && !\is_dir($concurrentDirectory)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        $uploadableClasses = $this->metadataReader->getUploadableClasses();
        foreach ($uploadableClasses as $class) {
            $this->metadataReader->getUploadableFields($class);
            $files[] = $class;
        }
        // TODO it could be nice if we return $files, to allow to exploit preloading...
        return [];
    }

    public function isOptional(): bool
    {
        return true;
    }
}
