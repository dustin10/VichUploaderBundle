<?php

namespace Vich\UploaderBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Vich\UploaderBundle\DependencyInjection\VichUploaderExtension;

class VichUploaderExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return [
            new VichUploaderExtension(),
        ];
    }

    protected function getMinimalConfiguration()
    {
        return [
            'db_driver' => 'propel',
        ];
    }

    public function setUp()
    {
        parent::setUp();

        $this->container->setParameter('kernel.bundles', []);
        $this->container->setParameter('kernel.cache_dir', sys_get_temp_dir());
    }

    public function testStorageServiceParameterIsSet()
    {
        $this->load([
            'storage' => 'gaufrette',
        ]);

        $this->assertContainerBuilderHasAlias('vich_uploader.storage', 'vich_uploader.storage.gaufrette');
    }

    public function testStorageServiceCustom()
    {
        $this->load([
            'storage' => '@acme.storage',
        ]);

        $this->assertContainerBuilderHasAlias('vich_uploader.storage', 'acme.storage');
    }

    public function testExtraServiceFilesAreLoaded()
    {
        $this->load([
            'twig' => true,
            'storage' => 'flysystem',
        ]);

        $this->assertContainerBuilderHasService('vich_uploader.storage.flysystem', 'Vich\UploaderBundle\Storage\FlysystemStorage');
        $this->assertContainerBuilderHasService('vich_uploader.twig.extension.uploader', 'Vich\UploaderBundle\Twig\Extension\UploaderExtension');
    }

    public function testMappingsServiceParameterIsSet()
    {
        $this->load([
            'mappings' => $mappings = [
                'foo' => [
                    'upload_destination' => 'web/',
                    'uri_prefix' => '/',
                    'namer' => ['service' => null, 'options' => null],
                    'directory_namer' => ['service' => null, 'options' => null],
                    'delete_on_remove' => true,
                    'delete_on_update' => true,
                    'inject_on_load' => true,
                ],
            ],
        ]);

        // the default db_driver is copied into the mapping
        $mappings['foo']['db_driver'] = 'propel';

        $this->assertContainerBuilderHasParameter('vich_uploader.mappings', $mappings);
    }

    public function testDbDriverIsntOverriden()
    {
        $this->load([
            'db_driver' => 'propel',
            'mappings' => $mappings = [
                'foo' => [
                    'upload_destination' => 'web/',
                    'uri_prefix' => '/',
                    'namer' => ['service' => null, 'options' => null],
                    'directory_namer' => ['service' => null, 'options' => null],
                    'delete_on_remove' => true,
                    'delete_on_update' => true,
                    'inject_on_load' => true,
                    'db_driver' => 'orm',
                ],
            ],
        ]);

        $this->assertContainerBuilderHasParameter('vich_uploader.mappings', $mappings);
    }
}
