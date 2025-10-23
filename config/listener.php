<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\EventListener\Doctrine\CleanListener;
use Vich\UploaderBundle\EventListener\Doctrine\InjectListener;
use Vich\UploaderBundle\EventListener\Doctrine\RemoveListener;
use Vich\UploaderBundle\EventListener\Doctrine\UploadListener;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // doctrine listeners base
    $services->set('vich_uploader.listener.doctrine.base')
        ->abstract()
        ->args([
            null, // will contain the mapping name
            null, // adapter
            service('vich_uploader.metadata_reader'),
            service('vich_uploader.upload_handler'),
        ]);

    // ORM listeners
    $services->set('vich_uploader.listener.inject.orm', InjectListener::class)
        ->parent('vich_uploader.listener.doctrine.base');

    $services->set('vich_uploader.listener.upload.orm', UploadListener::class)
        ->parent('vich_uploader.listener.doctrine.base');

    $services->set('vich_uploader.listener.clean.orm', CleanListener::class)
        ->parent('vich_uploader.listener.doctrine.base');

    $services->set('vich_uploader.listener.remove.orm', RemoveListener::class)
        ->parent('vich_uploader.listener.doctrine.base');

    // MongoDB listeners (inherit from ORM)
    $services->set('vich_uploader.listener.inject.mongodb')
        ->parent('vich_uploader.listener.inject.orm');

    $services->set('vich_uploader.listener.upload.mongodb')
        ->parent('vich_uploader.listener.upload.orm');

    $services->set('vich_uploader.listener.clean.mongodb')
        ->parent('vich_uploader.listener.clean.orm');

    $services->set('vich_uploader.listener.remove.mongodb')
        ->parent('vich_uploader.listener.remove.orm');

    // PHPCR listeners (inherit from ORM)
    $services->set('vich_uploader.listener.inject.phpcr')
        ->parent('vich_uploader.listener.inject.orm');

    $services->set('vich_uploader.listener.upload.phpcr')
        ->parent('vich_uploader.listener.upload.orm');

    $services->set('vich_uploader.listener.clean.phpcr')
        ->parent('vich_uploader.listener.clean.orm');

    $services->set('vich_uploader.listener.remove.phpcr')
        ->parent('vich_uploader.listener.remove.orm');
};
