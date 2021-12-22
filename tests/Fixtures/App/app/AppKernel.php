<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

            new Vich\UploaderBundle\VichUploaderBundle(),
            new Vich\TestBundle\VichTestBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        if (Kernel::MAJOR_VERSION > 4) {
            $loader->load(__DIR__.'/config/config.yml');
        } else {
            $loader->load(__DIR__.'/config/config-sf4.yml');
        }
    }

    public function getCacheDir(): string
    {
        return \sys_get_temp_dir().'/VichUploaderBundle/cache';
    }

    public function getLogDir(): string
    {
        return \sys_get_temp_dir().'/VichUploaderBundle/logs';
    }

    public function getProjectDir(): string
    {
        return __DIR__.'/../';
    }
}
