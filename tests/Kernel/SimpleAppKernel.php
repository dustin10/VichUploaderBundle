<?php

namespace Vich\UploaderBundle\Tests\Kernel;

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
        return [new FrameworkBundle(), new VichUploaderBundle()];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('framework', ['secret' => '$ecret']);
            $container->loadFromExtension('vich_uploader', ['db_driver' => 'orm']);
        });
    }
}
