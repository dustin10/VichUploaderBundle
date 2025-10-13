<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vich\UploaderBundle\Adapter\ODM\MongoDB\MongoDBAdapter;
use Vich\UploaderBundle\Adapter\ORM\DoctrineORMAdapter;
use Vich\UploaderBundle\Adapter\PHPCR\PHPCRAdapter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('vich_uploader.adapter.mongodb', MongoDBAdapter::class);
    $services->set('vich_uploader.adapter.orm', DoctrineORMAdapter::class);
    $services->set('vich_uploader.adapter.phpcr', PHPCRAdapter::class);
};
