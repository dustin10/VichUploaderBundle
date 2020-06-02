<?php

namespace Vich\UploaderBundle\Tests\Kernel;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Vich\UploaderBundle\Naming\OrignameNamer;
use Vich\UploaderBundle\VichUploaderBundle;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class FilesystemAppKernel extends Kernel
{
    use AppKernelTrait;

    public function registerBundles(): array
    {
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
            ]);

            $container->loadFromExtension('vich_uploader', [
                'db_driver' => 'orm',
                'mappings' => [
                    'product_image' => [
                        'uri_prefix' => '/images/products',
                        'upload_destination' => '%kernel.project_dir%/public/images/products',
                        'namer' => OrignameNamer::class,
                    ],
                ],
            ]);
        });
    }
}
