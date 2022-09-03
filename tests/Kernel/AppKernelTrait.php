<?php

namespace Vich\UploaderBundle\Tests\Kernel;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
trait AppKernelTrait
{
    public function getCacheDir(): string
    {
        return $this->createTmpDir('cache');
    }

    public function getLogDir(): string
    {
        return $this->createTmpDir('logs');
    }

    private function createTmpDir(string $type): string
    {
        $dir = \sys_get_temp_dir().'/VichUploaderBundle/'.\uniqid($type.'_', true);

        if (!\file_exists($dir)) {
            \mkdir($dir, 0o777, true);
        }

        return $dir;
    }
}
