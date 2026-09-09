<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingResolver;
use Vich\UploaderBundle\Naming\ConfigurableInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * Namers implementing only the deprecated ConfigurableInterface have no copy contract, so the
 * shared service keeps being configured in place, as before 3.1.
 */
#[AllowMockObjectsWithoutExpectations]
final class PropertyMappingResolverLegacyConfigurableTest extends TestCase
{
    private const NAMER_SERVICE = 'namer_service';

    private const NO_NAMER = ['service' => null, 'options' => null];

    private const DEPRECATION = 'Since vich/uploader-bundle 3.1: Configuring namer "namer_service" through "Vich\UploaderBundle\Naming\ConfigurableInterface" is deprecated, implement "Vich\UploaderBundle\Naming\ImmutableConfigurableInterface" instead.';

    #[IgnoreDeprecations]
    public function testTheSharedServiceIsConfiguredInPlace(): void
    {
        $this->expectUserDeprecationMessage(self::DEPRECATION);

        $namer = self::createLegacyNamer();
        $resolver = $this->createResolver($namer, ['prefix' => 'configured']);
        $object = new \stdClass();

        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'configured']);

        self::assertSame($namer, $mapping->getNamer());
        self::assertSame('configured', $mapping->getNamer()->name($object, $this->createFileMapping()));
    }

    public function testAMappingWithoutOptionsDoesNotConfigureTheNamer(): void
    {
        $namer = self::createLegacyNamer();
        $namer->configure(['prefix' => 'service']);
        $resolver = $this->createResolver($namer, []);
        $object = new \stdClass();

        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'plain']);

        self::assertSame($namer, $mapping->getNamer());
        self::assertSame('service', $mapping->getNamer()->name($object, $this->createFileMapping()));
    }

    #[IgnoreDeprecations]
    public function testKeepExtensionIsStillAcceptedByLegacyNamers(): void
    {
        $this->expectUserDeprecationMessage(self::DEPRECATION);

        $namer = self::createLegacyNamer();
        $resolver = $this->createResolver($namer, [], true);
        $object = new \stdClass();

        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'configured']);

        self::assertSame('keeping', $mapping->getNamer()->name($object, $this->createFileMapping()));
    }

    public function testANamerImplementingNeitherInterfaceCanNotReceiveOptions(): void
    {
        $namer = new class() implements NamerInterface {
            public function name(object|array $object, PropertyMappingInterface $mapping): string
            {
                return 'plain';
            }
        };
        $resolver = $this->createResolver($namer, ['prefix' => 'configured']);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Namer namer_service can not receive options as it does not implement ImmutableConfigurableInterface.');
        $resolver->resolve(new \stdClass(), 'file', ['mapping' => 'configured']);
    }

    private static function createLegacyNamer(): NamerInterface&ConfigurableInterface
    {
        return new class() implements NamerInterface, ConfigurableInterface {
            private string $prefix = 'default';

            public function configure(array $options): void
            {
                if (isset($options['keep_extension'])) {
                    $this->prefix = 'keeping';
                }
                $this->prefix = $options['prefix'] ?? $this->prefix;
            }

            public function name(object|array $object, PropertyMappingInterface $mapping): string
            {
                return $this->prefix;
            }
        };
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createResolver(NamerInterface $namer, array $options, bool $keepExtension = false): PropertyMappingResolver
    {
        return new PropertyMappingResolver(
            [self::NAMER_SERVICE => $namer],
            [],
            [
                'configured' => self::createMappingConfiguration(['service' => self::NAMER_SERVICE, 'options' => $options], $keepExtension),
                'plain' => self::createMappingConfiguration(['service' => self::NAMER_SERVICE, 'options' => []]),
            ]
        );
    }

    /**
     * @param array<string, mixed> $namer
     *
     * @return array<string, mixed>
     */
    private static function createMappingConfiguration(array $namer, bool $keepExtension = false): array
    {
        return [
            'upload_destination' => '/tmp',
            'uri_prefix' => '/',
            'namer' => $namer,
            'directory_namer' => self::NO_NAMER,
            'delete_on_remove' => true,
            'erase_fields' => true,
            'delete_on_update' => true,
            'inject_on_load' => false,
            'namer_keep_extension' => $keepExtension,
            'db_driver' => 'orm',
        ];
    }

    private function createFileMapping(): PropertyMappingInterface
    {
        $file = $this->getUploadedFileMock();
        $file->method('getClientOriginalName')->willReturn('Fôo Bàr.txt');
        $file->method('guessExtension')->willReturn('txt');

        $mapping = $this->getPropertyMappingMock();
        $mapping->method('getFile')->willReturn($file);

        return $mapping;
    }
}
