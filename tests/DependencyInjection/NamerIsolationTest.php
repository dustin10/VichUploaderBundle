<?php

declare(strict_types=1);

namespace Vich\UploaderBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Vich\UploaderBundle\DependencyInjection\VichUploaderExtension;
use Vich\UploaderBundle\Naming\ConfigurableDirectoryNamer;
use Vich\UploaderBundle\Naming\UniqidNamer;

/**
 * Verifies that when the same namer class is used in multiple mappings, each mapping gets
 * its own child service definition with a distinct service ID — preventing tagged-iterator
 * key collisions and cross-mapping state bleed from configure() calls.
 */
final class NamerIsolationTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [new VichUploaderExtension()];
    }

    protected function getMinimalConfiguration(): array
    {
        return ['db_driver' => 'orm'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->container->setParameter('kernel.bundles', []);
        $this->container->setParameter('kernel.bundles_metadata', []);
        $this->container->setParameter('kernel.root_dir', __DIR__.'/../Fixtures/App/app');
        $this->container->setParameter('kernel.project_dir', __DIR__.'/../Fixtures/App');
        $this->container->setParameter('kernel.cache_dir', \sys_get_temp_dir());
        $this->container->setParameter('kernel.build_dir', \sys_get_temp_dir());
        $this->container->setParameter('kernel.debug', false);
    }

    public function testSameFileNamerClassInTwoMappingsProducesTwoDistinctChildServices(): void
    {
        $this->load([
            'mappings' => [
                'products' => [
                    'upload_destination' => '/tmp/products',
                    'namer' => UniqidNamer::class,
                ],
                'invoices' => [
                    'upload_destination' => '/tmp/invoices',
                    'namer' => UniqidNamer::class,
                ],
            ],
        ]);

        $idProducts = UniqidNamer::class.'.products';
        $idInvoices = UniqidNamer::class.'.invoices';

        $this->assertContainerBuilderHasService($idProducts);
        $this->assertContainerBuilderHasService($idInvoices);

        // The two child definitions must be distinct service objects.
        self::assertNotSame(
            $this->container->getDefinition($idProducts),
            $this->container->getDefinition($idInvoices),
        );

        // The vich_uploader.mappings parameter must store the child service IDs, not the original class.
        $mappings = $this->container->getParameter('vich_uploader.mappings');
        self::assertSame($idProducts, $mappings['products']['namer']['service']);
        self::assertSame($idInvoices, $mappings['invoices']['namer']['service']);
    }

    public function testSameDirectoryNamerClassInTwoMappingsProducesTwoDistinctChildServices(): void
    {
        $this->load([
            'mappings' => [
                'products' => [
                    'upload_destination' => '/tmp/products',
                    'directory_namer' => [
                        'service' => ConfigurableDirectoryNamer::class,
                        'options' => ['directory_path' => 'products/'],
                    ],
                ],
                'invoices' => [
                    'upload_destination' => '/tmp/invoices',
                    'directory_namer' => [
                        'service' => ConfigurableDirectoryNamer::class,
                        'options' => ['directory_path' => 'invoices/'],
                    ],
                ],
            ],
        ]);

        $idProducts = ConfigurableDirectoryNamer::class.'.products';
        $idInvoices = ConfigurableDirectoryNamer::class.'.invoices';

        $this->assertContainerBuilderHasService($idProducts);
        $this->assertContainerBuilderHasService($idInvoices);

        self::assertNotSame(
            $this->container->getDefinition($idProducts),
            $this->container->getDefinition($idInvoices),
        );

        $mappings = $this->container->getParameter('vich_uploader.mappings');
        self::assertSame($idProducts, $mappings['products']['directory_namer']['service']);
        self::assertSame($idInvoices, $mappings['invoices']['directory_namer']['service']);
    }
}
