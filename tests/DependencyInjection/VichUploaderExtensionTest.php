<?php

namespace Vich\UploaderBundle\Tests\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Vich\UploaderBundle\DependencyInjection\VichUploaderExtension;
use Vich\UploaderBundle\Metadata\Driver\AttributeReader;
use Vich\UploaderBundle\Storage\FlysystemStorage;
use Vich\UploaderBundle\Twig\Extension\UploaderExtension;

class VichUploaderExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new VichUploaderExtension(),
        ];
    }

    protected function getMinimalConfiguration(): array
    {
        return [
            'db_driver' => 'orm',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->container->setParameter('kernel.bundles', []);
        $this->container->setParameter('kernel.bundles_metadata', []);
        $this->container->setParameter('kernel.root_dir', __DIR__.'/../Fixtures/App/app');
        $this->container->setParameter('kernel.project_dir', __DIR__.'/../Fixtures/App');
        $this->container->setParameter('kernel.cache_dir', \sys_get_temp_dir());
        $this->container->setParameter('kernel.debug', true);
    }

    public function testStorageServiceParameterIsSet(): void
    {
        $this->load([
            'storage' => 'gaufrette',
        ]);

        $this->assertContainerBuilderHasAlias('vich_uploader.storage', 'vich_uploader.storage.gaufrette');
    }

    public function testStorageServiceCustom(): void
    {
        $this->load([
            'storage' => '@acme.storage',
        ]);

        $this->assertContainerBuilderHasAlias('vich_uploader.storage', 'acme.storage');
    }

    public function testExtraServiceFilesAreLoaded(): void
    {
        $this->load([
            'twig' => true,
            'storage' => 'flysystem',
        ]);

        $this->assertContainerBuilderHasService('vich_uploader.storage.flysystem', FlysystemStorage::class);
        $this->assertContainerBuilderHasService(UploaderExtension::class);
    }

    public function testMappingsServiceParameterIsSet(): void
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
        $mappings['foo']['db_driver'] = 'orm';

        $this->assertContainerBuilderHasParameter('vich_uploader.mappings', $mappings);
    }

    public function testDbDriverIsNotOverridden(): void
    {
        $this->load([
            'db_driver' => 'orm',
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

    public function testListenersCreation(): void
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

    public function testFormThemeCorrectlyOverridden(): void
    {
        $vichUploaderExtension = new VichUploaderExtension();
        $this->container->registerExtension($vichUploaderExtension);

        $twigExtension = new TwigExtension();
        $this->container->registerExtension($twigExtension);

        $twigExtension->load([[
            'strict_variables' => true,
            'exception_controller' => null, // TODO remove after bumping symfony/twig-bundle to ^5.0
            'form_themes' => ['@Ololo/trololo.html.twig'],
        ]], $this->container);
        $vichUploaderExtension->load([$this->getMinimalConfiguration()], $this->container);

        $this->assertContainerBuilderHasParameter(
            'twig.form.resources',
            ['@VichUploader/Form/fields.html.twig', 'form_div_layout.html.twig', '@Ololo/trololo.html.twig']
        );
    }

    public function testMetadataAnnotation(): void
    {
        $this->load([
            'metadata' => [
                'type' => 'annotation',
            ],
        ]);

        self::assertContainerBuilderHasService('vich_uploader.metadata.reader', AnnotationReader::class);
    }

    public function testMetadataAttribute(): void
    {
        $this->load([
            'metadata' => [
                'type' => 'attribute',
            ],
        ]);

        self::assertContainerBuilderHasService('vich_uploader.metadata.reader', AttributeReader::class);
    }
}
