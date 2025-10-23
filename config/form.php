<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('vich_uploader.form.type.file', VichFileType::class)
        ->public()
        ->args([
            service('vich_uploader.storage'),
            service('vich_uploader.upload_handler'),
            service('vich_uploader.property_mapping_factory'),
            service('property_accessor'),
        ])
        ->tag('form.type', ['alias' => 'vich_file']);

    $services->set('vich_uploader.form.type.image', VichImageType::class)
        ->public()
        ->args([
            service('vich_uploader.storage'),
            service('vich_uploader.upload_handler'),
            service('vich_uploader.property_mapping_factory'),
            service('property_accessor'),
            service('liip_imagine.cache.manager')->nullOnInvalid(),
        ])
        ->tag('form.type', ['alias' => 'vich_image']);

    $services->alias(VichFileType::class, 'vich_uploader.form.type.file');
    $services->alias(VichImageType::class, 'vich_uploader.form.type.image');
};
