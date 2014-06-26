<?php

namespace Vich\UploaderBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

use Vich\UploaderBundle\DependencyInjection\VichUploaderExtension;

class VichUploaderExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new VichUploaderExtension()
        );
    }

    protected function getMinimalConfiguration()
    {
        return array(
            'db_driver' => 'propel',
        );
    }

    public function setUp()
    {
        parent::setUp();

        $this->container->setParameter('kernel.bundles', array());
        $this->container->setParameter('kernel.cache_dir', sys_get_temp_dir());
    }

    public function testStorageServiceParameterIsSet()
    {
        $this->load(array(
            'storage' => 'gaufrette',
        ));

        $this->assertContainerBuilderHasParameter('vich_uploader.storage_service', 'gaufrette');
    }

    public function testExtraServiceFilesAreLoaded()
    {
        $this->load(array(
            'twig'      => true,
            'gaufrette' => true,
        ));

        $this->assertContainerBuilderHasService('vich_uploader.storage.gaufrette', 'Vich\UploaderBundle\Storage\GaufretteStorage');
        $this->assertContainerBuilderHasService('vich_uploader.twig.extension.uploader', 'Vich\UploaderBundle\Twig\Extension\UploaderExtension');
    }

    public function testMappingsServiceParameterIsSet()
    {
        $this->load(array(
            'mappings' => $mappings = array(
                'foo' => array(
                    'upload_destination'    => 'web/',
                    'uri_prefix'            => '/',
                    'namer'                 => null,
                    'directory_namer'       => null,
                    'delete_on_remove'      => true,
                    'delete_on_update'      => true,
                    'inject_on_load'        => true,
                )
            ),
        ));

        // the default db_driver is copied into the mapping
        $mappings['foo']['db_driver'] = 'propel';

        $this->assertContainerBuilderHasParameter('vich_uploader.mappings', $mappings);
    }

    public function testDbDriverIsntOverriden()
    {
        $this->load(array(
            'db_driver' => 'propel',
            'mappings' => $mappings = array(
                'foo' => array(
                    'upload_destination'    => 'web/',
                    'uri_prefix'            => '/',
                    'namer'                 => null,
                    'directory_namer'       => null,
                    'delete_on_remove'      => true,
                    'delete_on_update'      => true,
                    'inject_on_load'        => true,
                    'db_driver'             => 'orm',
                )
            ),
        ));

        $this->assertContainerBuilderHasParameter('vich_uploader.mappings', $mappings);
    }
}
