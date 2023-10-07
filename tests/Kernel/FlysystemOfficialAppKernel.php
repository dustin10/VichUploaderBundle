<?php

namespace Vich\UploaderBundle\Tests\Kernel;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use League\FlysystemBundle\FlysystemBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Vich\UploaderBundle\VichUploaderBundle;
use Vich\UploaderBundle\Naming\UniqidNamer;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class FlysystemOfficialAppKernel extends Kernel
{
    use AppKernelTrait;

    public function registerBundles(): array
    {
        return [new FrameworkBundle(), new DoctrineBundle(), new FlysystemBundle(), new VichUploaderBundle()];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(static function (ContainerBuilder $container): void {
            $container->loadFromExtension('framework', [
                'secret' => '$ecret',
                'router' => [
                    'resource' => 'kernel::loadRoutes',
                    'type' => 'service',
                    'utf8' => false,
                ],
                'http_method_override' => false,
            ]);

            $container->loadFromExtension('doctrine', [
                'dbal' => [
                    'driver' => 'pdo_sqlite',
                    'memory' => true,
                    'charset' => 'UTF8',
                ],
            ]);

            $container->loadFromExtension('flysystem', [
                'storages' => [
                    'uploads.storage' => ['adapter' => 'memory'],
                ],
            ]);

            $container->loadFromExtension('vich_uploader', [
                'db_driver' => 'orm',
                'storage' => 'flysystem',
                'mappings' => [
                    'product_image' => [
                        'uri_prefix' => '/images/products',
                        'upload_destination' => 'uploads.storage',
                        'namer' => UniqidNamer::class,
                    ],
                ],
            ]);

            $container->setAlias('test.vich_uploader.storage', 'vich_uploader.storage')->setPublic(true);
            $container->setAlias('test.uploads.storage', 'uploads.storage')->setPublic(true);
        });
    }
}
