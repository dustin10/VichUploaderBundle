<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\DataCollector\MappingCollector;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(MappingCollector::class)
        ->args([
            service('vich_uploader.metadata_reader'),
        ])
        ->tag('data_collector', [
            'template' => '@VichUploader/Collector/mapping_collector.html.twig',
            'id' => 'vich_uploader.mapping_collector',
        ]);
};
