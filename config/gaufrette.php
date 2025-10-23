<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\Storage\GaufretteStorage;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('vich_uploader.storage.gaufrette', GaufretteStorage::class)
        ->args([
            service('vich_uploader.property_mapping_factory'),
            service('knp_gaufrette.filesystem_map'),
            param('knp_gaufrette.stream_wrapper.protocol'),
        ]);

    $services->alias(GaufretteStorage::class, 'vich_uploader.storage.gaufrette');
};
