<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\Storage\FlysystemStorage;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('vich_uploader.storage.flysystem', FlysystemStorage::class)
        ->args([
            service('vich_uploader.property_mapping_factory'),
            null, // Populated by RegisterFlysystemRegistryPass
            param('vich_uploader.use_flysystem_to_resolve_uri'),
        ]);

    $services->alias(FlysystemStorage::class, 'vich_uploader.storage.flysystem');
};
