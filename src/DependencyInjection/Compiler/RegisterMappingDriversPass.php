<?php

namespace Vich\UploaderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
final class RegisterMappingDriversPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $drivers = [
            new Reference('vich_uploader.metadata_driver.xml'),
        ];

        $managers = [];
        if ($container->hasDefinition('doctrine_mongodb')) {
            $managers[] = new Reference('doctrine_mongodb');
        }
        if ($container->hasDefinition('doctrine')) {
            $managers[] = new Reference('doctrine');
        }
        if ($container->hasDefinition('doctrine_phpcr')) {
            $managers[] = new Reference('doctrine_phpcr');
        }

        if (count($managers) > 0) {
            $drivers[] = $container->getDefinition('vich_uploader.metadata_driver.annotation')
                ->replaceArgument('$managerRegistryList', $managers);
        }

        if (\class_exists(Yaml::class)) {
            $drivers[] = new Reference('vich_uploader.metadata_driver.yaml');
            $drivers[] = new Reference('vich_uploader.metadata_driver.yml');
        }

        $container
            ->getDefinition('vich_uploader.metadata_driver.chain')
            ->replaceArgument(0, $drivers);
    }
}
