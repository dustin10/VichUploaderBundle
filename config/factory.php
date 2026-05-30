<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Mapping\PropertyMappingResolver;
use Vich\UploaderBundle\Mapping\PropertyMappingResolverInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('vich_uploader.property_mapping_resolver', PropertyMappingResolver::class)
        ->args([
            tagged_iterator('vich_uploader.namer', defaultIndexMethod: 'getId'),
            tagged_iterator('vich_uploader.dir_namer', defaultIndexMethod: 'getId'),
            param('vich_uploader.mappings'),
            param('vich_uploader.default_filename_attribute_suffix'),
        ]);

    $services->alias(PropertyMappingResolverInterface::class, 'vich_uploader.property_mapping_resolver');

    $services->set('vich_uploader.property_mapping_factory', PropertyMappingFactory::class)
        ->args([
            service('vich_uploader.metadata_reader'),
            service('vich_uploader.property_mapping_resolver'),
        ]);

    $services->alias(PropertyMappingFactory::class, 'vich_uploader.property_mapping_factory');
};
