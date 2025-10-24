<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Metadata\Cache\FileCache;
use Metadata\Driver\DriverChain;
use Metadata\Driver\FileLocator;
use Metadata\MetadataFactory;
use Vich\UploaderBundle\Metadata\CacheWarmer;
use Vich\UploaderBundle\Metadata\Driver\AnnotationDriver;
use Vich\UploaderBundle\Metadata\Driver\AttributeDriver;
use Vich\UploaderBundle\Metadata\Driver\AttributeReader;
use Vich\UploaderBundle\Metadata\Driver\XmlDriver;
use Vich\UploaderBundle\Metadata\Driver\YamlDriver;
use Vich\UploaderBundle\Metadata\Driver\YmlDriver;
use Vich\UploaderBundle\Metadata\MetadataReader;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // file locator
    $services->set('vich_uploader.metadata.file_locator', FileLocator::class)
        ->args([
            [], // namespace prefixes mapping to directories, injected by extension
        ]);

    // reader
    $services->set('vich_uploader.metadata.attribute_reader', AttributeReader::class);

    // drivers
    $services->set('vich_uploader.metadata_driver.attribute', AttributeDriver::class)
        ->arg('$reader', service('vich_uploader.metadata.reader'))
        ->arg('$managerRegistryList', null); // replaced by compiler pass

    $services->set('vich_uploader.metadata_driver.annotation', AnnotationDriver::class)
        ->arg('$reader', service('vich_uploader.metadata.reader'))
        ->arg('$managerRegistryList', null) // replaced by compiler pass
        ->deprecate('vich/uploader-bundle', '2.9', 'The "%service_id%" service is deprecated, use "vich_uploader.metadata_driver.attribute" instead.');

    $services->set('vich_uploader.metadata_driver.xml', XmlDriver::class)
        ->args([
            service('vich_uploader.metadata.file_locator'),
        ]);

    $services->set('vich_uploader.metadata_driver.yml', YmlDriver::class)
        ->args([
            service('vich_uploader.metadata.file_locator'),
        ]);

    $services->set('vich_uploader.metadata_driver.yaml', YamlDriver::class)
        ->args([
            service('vich_uploader.metadata.file_locator'),
        ]);

    $services->set('vich_uploader.metadata_driver.chain', DriverChain::class)
        ->args([
            null, // injected by compiler pass
        ]);

    $services->alias('vich_uploader.metadata_driver', 'vich_uploader.metadata_driver.chain');

    // metadata services
    $services->set('vich_uploader.metadata.cache.file_cache', FileCache::class)
        ->args([
            null, // cache directory, injected by extension
        ]);

    $services->alias('vich_uploader.metadata.cache', 'vich_uploader.metadata.cache.file_cache');

    $services->set('vich_uploader.metadata_factory', MetadataFactory::class)
        ->args([
            service('vich_uploader.metadata_driver'),
            'Metadata\ClassHierarchyMetadata',
            param('kernel.debug'),
        ])
        ->call('setCache', [
            service('vich_uploader.metadata.cache')->ignoreOnInvalid(),
        ]);

    $services->set('vich_uploader.metadata_reader', MetadataReader::class)
        ->args([
            service('vich_uploader.metadata_factory'),
        ]);

    // cache warmer
    $services->set(CacheWarmer::class)
        ->args([
            null, // cache directory, injected by extension
            service('vich_uploader.metadata_reader'),
        ])
        ->tag('kernel.cache_warmer');
};
