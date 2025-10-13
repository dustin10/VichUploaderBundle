<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\Naming\Base64Namer;
use Vich\UploaderBundle\Naming\ConfigurableDirectoryNamer;
use Vich\UploaderBundle\Naming\CurrentDateTimeDirectoryNamer;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\HashNamer;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\OrignameNamer;
use Vich\UploaderBundle\Naming\PropertyDirectoryNamer;
use Vich\UploaderBundle\Naming\PropertyNamer;
use Vich\UploaderBundle\Naming\SmartUniqueNamer;
use Vich\UploaderBundle\Naming\SubdirDirectoryNamer;
use Vich\UploaderBundle\Naming\UniqidNamer;
use Vich\UploaderBundle\Util\Transliterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // instanceof configuration for auto-tagging
    $services->instanceof(NamerInterface::class)
        ->autowire()
        ->tag('vich_uploader.namer');

    $services->instanceof(DirectoryNamerInterface::class)
        ->autowire()
        ->tag('vich_uploader.dir_namer');

    // Namer services
    $services->set(UniqidNamer::class);

    $services->set(PropertyNamer::class)
        ->args([
            service(Transliterator::class),
        ]);

    $services->set(OrignameNamer::class)
        ->args([
            service(Transliterator::class),
        ]);

    $services->set(HashNamer::class);

    $services->set(Base64Namer::class);

    $services->set(SubdirDirectoryNamer::class);

    $services->set(PropertyDirectoryNamer::class)
        ->args([
            service('property_accessor')->nullOnInvalid(),
            service(Transliterator::class),
        ]);

    $services->set(CurrentDateTimeDirectoryNamer::class)
        ->args([
            service('property_accessor')->nullOnInvalid(),
        ]);

    $services->set(ConfigurableDirectoryNamer::class);

    $services->set(SmartUniqueNamer::class)
        ->args([
            service(Transliterator::class),
        ]);

    // Backward compatibility aliases
    $services->alias('vich_uploader.namer_uniqid', UniqidNamer::class);
    $services->alias('vich_uploader.namer_property', PropertyNamer::class);
    $services->alias('vich_uploader.namer_origname', OrignameNamer::class);
    $services->alias('vich_uploader.namer_hash', HashNamer::class);
    $services->alias('vich_uploader.namer_base64', Base64Namer::class);
    $services->alias('vich_uploader.directory_namer_subdir', SubdirDirectoryNamer::class);
    $services->alias('vich_uploader.namer_directory_property', PropertyDirectoryNamer::class);
    $services->alias('vich_uploader.namer_directory_current_date_time', CurrentDateTimeDirectoryNamer::class);
    $services->alias('vich_uploader.namer_directory_configurable', ConfigurableDirectoryNamer::class);
    $services->alias('vich_uploader.namer_smart_unique', SmartUniqueNamer::class);

    // Transliterator service
    $services->set(Transliterator::class)
        ->args([
            service('slugger'),
        ]);
};
