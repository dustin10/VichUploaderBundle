<?php

namespace Vich\UploaderBundle\Tests\Kernel;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Oneup\FlysystemBundle\OneupFlysystemBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Vich\UploaderBundle\Naming\UniqidNamer;
use Vich\UploaderBundle\VichUploaderBundle;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class FlysystemOneUpAppKernel extends Kernel
{
    use AppKernelTrait;

    public function registerBundles(): array
    {
        if (\class_exists(OneupFlysystemBundle::class)) {
            return [new FrameworkBundle(), new DoctrineBundle(), new OneupFlysystemBundle(), new VichUploaderBundle()];
        }

        return [new FrameworkBundle(), new VichUploaderBundle()];
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

            $container->loadFromExtension('oneup_flysystem', [
                'adapters' => ['memory_adapter' => ['memory' => []]],
                'filesystems' => [
                    'product_image_fs' => ['adapter' => 'memory_adapter', 'mount' => 'product_image_fs'],
                ],
            ]);

            $container->loadFromExtension('vich_uploader', [
                'db_driver' => 'orm',
                'storage' => 'flysystem',
                'mappings' => [
                    'product_image' => [
                        'uri_prefix' => '/images/products',
                        'upload_destination' => 'oneup_flysystem.product_image_fs',
                        'namer' => UniqidNamer::class,
                    ],
                ],
            ]);

            $container->setAlias('test.vich_uploader.storage', 'vich_uploader.storage')->setPublic(true);
        });
    }
}
