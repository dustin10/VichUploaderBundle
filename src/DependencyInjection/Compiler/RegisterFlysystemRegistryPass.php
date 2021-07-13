<?php

namespace Vich\UploaderBundle\DependencyInjection\Compiler;

use League\FlysystemBundle\FlysystemBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @internal
 */
final class RegisterFlysystemRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('vich_uploader.storage.flysystem')) {
            return;
        }

        $storageDefinition = $container->getDefinition('vich_uploader.storage.flysystem');

        // OneupFlysystemBundle
        if ($container->hasDefinition('oneup_flysystem.filesystem')) {
            $registry = [];
            foreach ($container->findTaggedServiceIds('oneup_flysystem.filesystem') as $serviceId => $tags) {
                foreach ($tags as $tag) {
                    if (isset($tag['mount'])) {
                        $service = 'oneup_flysystem.'.$tag['mount'].'_filesystem';
                        $registry[$service] = new Reference($service);
                    }
                }
            }

            $storageDefinition->replaceArgument(1, ServiceLocatorTagPass::register($container, $registry));

            return;
        }

        // League\FlysystemBundle
        if (\class_exists(FlysystemBundle::class)) {
            $registry = [];
            foreach ($container->findTaggedServiceIds('flysystem.storage') as $serviceId => $tags) {
                foreach ($tags as $tag) {
                    if (isset($tag['storage'])) {
                        $registry[$tag['storage']] = new Reference($serviceId);
                    }
                }
            }

            $storageDefinition->replaceArgument(1, ServiceLocatorTagPass::register($container, $registry));
        }
    }
}
