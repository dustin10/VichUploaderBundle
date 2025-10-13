<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\Injector\FileInjector;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('vich_uploader.file_injector', FileInjector::class)
        ->args([
            service('vich_uploader.storage'),
        ]);
};
