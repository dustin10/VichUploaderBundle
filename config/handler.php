<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\Handler\DownloadHandler;
use Vich\UploaderBundle\Handler\UploadHandler;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // download handler
    $services->set('vich_uploader.download_handler', DownloadHandler::class)
        ->public()
        ->args([
            service('vich_uploader.property_mapping_factory'),
            service('vich_uploader.storage'),
        ]);

    // global handler
    $services->set('vich_uploader.upload_handler', UploadHandler::class)
        ->public()
        ->args([
            service('vich_uploader.property_mapping_factory'),
            service('vich_uploader.storage'),
            service('vich_uploader.file_injector'),
            service('event_dispatcher'),
        ]);

    $services->alias(DownloadHandler::class, 'vich_uploader.download_handler');
    $services->alias(UploadHandler::class, 'vich_uploader.upload_handler');
};
