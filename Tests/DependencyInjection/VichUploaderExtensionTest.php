<?php

namespace Vich\UploaderBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
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

    protected function setUp()
    {
        parent::setUp();

        $this->container->setParameter('kernel.bundles', []);
        $this->container->setParameter('kernel.bundles_metadata', []);
        $this->container->setParameter('kernel.root_dir', __DIR__.'/../Fixtures/App/app');
        $this->container->setParameter('kernel.project_dir', __DIR__.'/../Fixtures/App');
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

    public function testDbDriverIsNotOverridden()
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

    public function testListenersCreation()
    {
        $this->load([
            'db_driver' => 'mongodb',
            'mappings' => $mappings = [
                'profile_common_avatar' => [
                    'upload_destination' => 'profile_common_user_avatar_images',
                    'uri_prefix' => '/',
                    'namer' => ['service' => null, 'options' => null],
                    'directory_namer' => ['service' => null, 'options' => null],
                    'delete_on_remove' => true,
                    'delete_on_update' => false,
                    'inject_on_load' => true,
                ],
            ],
        ]);

        $this->assertContainerBuilderHasService('vich_uploader.listener.inject.profile_common_avatar');
        $this->assertContainerBuilderNotHasService('vich_uploader.listener.clean.profile_common_avatar');
        $this->assertContainerBuilderHasService('vich_uploader.listener.remove.profile_common_avatar');
    }

    public function testFormThemeCorrectlyOverridden()
    {
        $vichUploaderExtension = new VichUploaderExtension();
        $this->container->registerExtension($vichUploaderExtension);

        $twigExtension = new TwigExtension();
        $this->container->registerExtension($twigExtension);

        $twigExtension->load([['form_themes' => ['@Ololo/trololo.html.twig']]], $this->container);
        $vichUploaderExtension->load([$this->getMinimalConfiguration()], $this->container);

        $this->assertContainerBuilderHasParameter(
            'twig.form.resources',
            ['@VichUploader/Form/fields.html.twig', 'form_div_layout.html.twig', '@Ololo/trololo.html.twig']
        );
    }
}
