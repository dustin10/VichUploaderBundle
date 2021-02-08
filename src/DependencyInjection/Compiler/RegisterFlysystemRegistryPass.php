<?php

namespace Vich\UploaderBundle\DependencyInjection\Compiler;

use League\Flysystem\FilesystemOperator;
use League\FlysystemBundle\FlysystemBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Vich\UploaderBundle\Storage\FlysystemV2Storage;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
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
        if ($container->hasDefinition('oneup_flysystem.mount_manager')) {
            $storageDefinition->replaceArgument(1, new Reference('oneup_flysystem.mount_manager'));

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

            if (\class_exists(FilesystemOperator::class)) {
                $storageDefinition->setClass(FlysystemV2Storage::class);
            }

            $storageDefinition->replaceArgument(1, ServiceLocatorTagPass::register($container, $registry));
        }
    }
}
