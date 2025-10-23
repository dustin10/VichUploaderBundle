<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\Storage\FileSystemStorage;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('vich_uploader.storage.file_system', FileSystemStorage::class)
        ->args([
            service('vich_uploader.property_mapping_factory'),
        ]);

    $services->alias(FileSystemStorage::class, 'vich_uploader.storage.file_system');
};
