<?php

namespace Vich\UploaderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

class RegisterMappingDriversPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $drivers = [
            new Reference('vich_uploader.metadata_driver.xml'),
        ];

        if ($container->has('annotation_reader')) {
            $drivers[] = new Reference('vich_uploader.metadata_driver.annotation');
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
