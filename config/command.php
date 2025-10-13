<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\Command\MappingDebugClassCommand;
use Vich\UploaderBundle\Command\MappingDebugCommand;
use Vich\UploaderBundle\Command\MappingListClassesCommand;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('vich_uploader.command.mapping_debug_class', MappingDebugClassCommand::class)
        ->args([
            service('vich_uploader.metadata_reader'),
        ])
        ->tag('console.command', ['command' => 'vich:mapping:debug-class']);

    $services->set('vich_uploader.command.mapping_debug', MappingDebugCommand::class)
        ->args([
            param('vich_uploader.mappings'),
        ])
        ->tag('console.command', ['command' => 'vich:mapping:debug']);

    $services->set('vich_uploader.command.mapping_list_classes', MappingListClassesCommand::class)
        ->args([
            service('vich_uploader.metadata_reader'),
        ])
        ->tag('console.command', ['command' => 'vich:mapping:list-classes']);
};
