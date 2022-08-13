<?php

namespace Vich\UploaderBundle\Tests\Kernel;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Vich\UploaderBundle\VichUploaderBundle;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class SimpleAppKernel extends Kernel
{
    use AppKernelTrait;

    public function registerBundles(): array
    {
        return [new FrameworkBundle(), new DoctrineBundle(), new VichUploaderBundle()];
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

            $container->loadFromExtension('vich_uploader', ['db_driver' => 'orm']);
        });
    }
}
