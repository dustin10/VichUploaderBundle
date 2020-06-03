<?php

namespace Vich\UploaderBundle\Tests\Kernel;

use League\FlysystemBundle\FlysystemBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Vich\UploaderBundle\VichUploaderBundle;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class FlysystemOfficialAppKernel extends Kernel
{
    use AppKernelTrait;

    public function registerBundles(): array
    {
        return [new FrameworkBundle(), new FlysystemBundle(), new VichUploaderBundle()];
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
                    ],
                ],
            ]);

            $container->setAlias('test.vich_uploader.storage', 'vich_uploader.storage')->setPublic(true);
            $container->setAlias('test.uploads.storage', 'uploads.storage')->setPublic(true);
        });
    }
}
