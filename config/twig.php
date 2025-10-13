<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Vich\UploaderBundle\Twig\Extension\UploaderExtension;
use Vich\UploaderBundle\Twig\Extension\UploaderExtensionRuntime;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(UploaderHelper::class)
        ->args([
            service('vich_uploader.storage'),
        ])
        ->tag('templating.helper', ['alias' => 'vich_uploader']);

    $services->set(UploaderExtension::class)
        ->tag('twig.extension');

    $services->set(UploaderExtensionRuntime::class)
        ->args([
            service(UploaderHelper::class),
        ])
        ->tag('twig.runtime');
};
