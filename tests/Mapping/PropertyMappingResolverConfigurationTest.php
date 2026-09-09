<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingResolver;
use Vich\UploaderBundle\Naming\Base64Namer;
use Vich\UploaderBundle\Naming\ConfigurableDirectoryNamer;
use Vich\UploaderBundle\Naming\CurrentDateTimeDirectoryNamer;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\HashNamer;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\OrignameNamer;
use Vich\UploaderBundle\Naming\PropertyDirectoryNamer;
use Vich\UploaderBundle\Naming\PropertyNamer;
use Vich\UploaderBundle\Naming\SlugNamer;
use Vich\UploaderBundle\Naming\SmartUniqueNamer;
use Vich\UploaderBundle\Naming\SubdirDirectoryNamer;
use Vich\UploaderBundle\Naming\UniqidNamer;
use Vich\UploaderBundle\Tests\TestCase;
use Vich\UploaderBundle\Util\Transliterator;

/**
 * Namers are shared services and configure() only overwrites the options it receives, so a mapping
 * which does not set an option must not inherit the value set by the mapping resolved before it.
 */
#[AllowMockObjectsWithoutExpectations]
final class PropertyMappingResolverConfigurationTest extends TestCase
{
    private const NAMER_SERVICE = 'namer_service';

    private const DIRECTORY_NAMER_SERVICE = 'directory_namer_service';

    private const NO_NAMER = ['service' => null, 'options' => null];

    /**
     * @return array<string, array{NamerInterface, array<string, mixed>, array<string, mixed>, string}>
     */
    public static function namerProvider(): array
    {
        $transliterator = new Transliterator(new AsciiSlugger());

        return [
            'keep_extension of the UniqidNamer' => [
                new UniqidNamer(),
                ['keep_extension' => true],
                [],
                '/\.txt$/',
            ],
            'keep_extension of the SlugNamer' => [
                new SlugNamer($transliterator, new class() {
                    public ?object $existingFile = null;

                    public function find(string $name): ?object
                    {
                        return $this->existingFile;
                    }
                }, 'find'),
                ['keep_extension' => true],
                [],
                '/\.txt$/',
            ],
            'transliterate of the OrignameNamer' => [
                new OrignameNamer($transliterator),
                ['transliterate' => true],
                [],
                '/^[a-z0-9]{13}_Fôo Bàr\.txt$/u',
            ],
            'transliterate of the PropertyNamer' => [
                new PropertyNamer($transliterator),
                ['property' => 'title', 'transliterate' => true],
                ['property' => 'title'],
                '/^Tîtle Wîth Accents\.txt$/u',
            ],
            'algorithm and length of the HashNamer' => [
                new HashNamer(),
                ['algorithm' => 'md5', 'length' => 8],
                [],
                '/^[[:xdigit:]]{40}\.txt$/',
            ],
            'length of the Base64Namer' => [
                new Base64Namer(),
                ['length' => 40],
                [],
                '/^[\w-]{10}\.txt$/',
            ],
        ];
    }

    #[DataProvider('namerProvider')]
    public function testNamerOptionsAreNotLeakedToAMappingSharingTheSameNamer(NamerInterface $namer, array $configuredOptions, array $plainOptions, string $expectedName): void
    {
        $resolver = $this->createNamerResolver($namer, $configuredOptions, $plainOptions);
        $object = self::createObject();

        $resolver->resolve($object, 'file', ['mapping' => 'configured', 'propertyName' => 'file']);
        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'plain', 'propertyName' => 'file']);

        self::assertMatchesRegularExpression($expectedName, $mapping->getNamer()->name($object, $this->createFileMapping(isset($configuredOptions['keep_extension']) ? 'Fôo Bàr.xyz' : 'Fôo Bàr.txt')));
    }

    /**
     * @return array<string, array{DirectoryNamerInterface, array<string, mixed>, array<string, mixed>, string}>
     */
    public static function directoryNamerProvider(): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return [
            'directory_path of the ConfigurableDirectoryNamer' => [
                new ConfigurableDirectoryNamer(),
                ['directory_path' => 'configured'],
                [],
                '',
            ],
            'chars_per_dir and dirs of the SubdirDirectoryNamer' => [
                new SubdirDirectoryNamer(),
                ['chars_per_dir' => 3, 'dirs' => 2],
                [],
                '01',
            ],
            'transliterate of the PropertyDirectoryNamer' => [
                new PropertyDirectoryNamer($propertyAccessor, new Transliterator(new AsciiSlugger())),
                ['property' => 'title', 'transliterate' => true],
                ['property' => 'title'],
                'Tîtle Wîth Accents',
            ],
            'date_time_format of the CurrentDateTimeDirectoryNamer' => [
                new CurrentDateTimeDirectoryNamer($propertyAccessor),
                ['date_time_property' => 'createdAt', 'date_time_format' => 'Y-m'],
                ['date_time_property' => 'createdAt'],
                '2018/09/23',
            ],
        ];
    }

    #[DataProvider('directoryNamerProvider')]
    public function testDirectoryNamerOptionsAreNotLeakedToAMappingSharingTheSameNamer(DirectoryNamerInterface $namer, array $configuredOptions, array $plainOptions, string $expectedName): void
    {
        $resolver = $this->createDirectoryNamerResolver($namer, $configuredOptions, $plainOptions);
        $object = self::createObject();

        $resolver->resolve($object, 'file', ['mapping' => 'configured', 'propertyName' => 'file']);
        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'plain', 'propertyName' => 'file']);

        self::assertSame($expectedName, $mapping->getDirectoryNamer()->directoryName($object, $this->createFileMapping()));
    }

    /**
     * The namer_keep_extension option is turned into a namer option by the resolver, so it leaks
     * the same way: a mapping which does not enable it kept the extension declared by the client
     * instead of the guessed one.
     */
    public function testKeepExtensionIsNotLeakedToAMappingSharingTheSameNamer(): void
    {
        $namer = new SmartUniqueNamer($this->getTransliterator());
        $resolver = $this->createNamerResolver($namer, [], [], true);
        $object = self::createObject();

        $resolver->resolve($object, 'file', ['mapping' => 'configured', 'propertyName' => 'file']);
        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'plain', 'propertyName' => 'file']);

        self::assertMatchesRegularExpression('/^test-[[:xdigit:]]{22}\.txt$/', $mapping->getNamer()->name($object, $this->createFileMapping('test.xyz')));
    }

    /**
     * The options belong to the mapping, so they are applied to a copy of the namer: the service
     * itself keeps its default configuration, whoever else uses it.
     */
    public function testTheSharedNamerServiceIsNotConfigured(): void
    {
        $namer = new HashNamer();
        $resolver = $this->createNamerResolver($namer, ['algorithm' => 'md5', 'length' => 8], []);
        $object = self::createObject();

        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'configured', 'propertyName' => 'file']);

        self::assertNotSame($namer, $mapping->getNamer());
        self::assertMatchesRegularExpression('/^[[:xdigit:]]{40}\.txt$/', $namer->name($object, $this->createFileMapping()));
    }

    public function testRetainedMappingsKeepTheirOptionsInBothResolutionOrders(): void
    {
        $resolver = $this->createNamerResolver(new SmartUniqueNamer($this->getTransliterator()), [], [], true);
        $object = self::createObject();
        $fileMapping = $this->createFileMapping('test.php');
        $plain = $resolver->resolve($object, 'file', ['mapping' => 'plain']);
        $keeping = $resolver->resolve($object, 'file', ['mapping' => 'configured']);
        $plainAgain = $resolver->resolve($object, 'file', ['mapping' => 'plain']);

        self::assertStringEndsWith('.txt', $plain->getNamer()->name($object, $fileMapping));
        self::assertStringEndsWith('.php', $keeping->getNamer()->name($object, $fileMapping));
        self::assertStringEndsWith('.txt', $plainAgain->getNamer()->name($object, $fileMapping));
    }

    public function testExplicitKeepExtensionOptionIsPreserved(): void
    {
        $resolver = $this->createNamerResolver(new SmartUniqueNamer($this->getTransliterator()), ['keep_extension' => true], []);
        $object = self::createObject();
        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'configured']);

        self::assertStringEndsWith('.xyz', $mapping->getNamer()->name($object, $this->createFileMapping('test.xyz')));
    }

    public function testServiceConfigurationIsPreservedWithoutMappingOptions(): void
    {
        $namer = new PropertyNamer($this->getTransliterator());
        $namer->configure(['property' => 'title']);
        $resolver = $this->createNamerResolver($namer, [], []);
        $object = self::createObject();
        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'plain']);

        self::assertSame('Tîtle Wîth Accents.txt', $mapping->getNamer()->name($object, $this->createFileMapping('Fôo Bàr.xyz')));
    }

    public function testSubclassesInheritTheConfigurationCopyContract(): void
    {
        $namer = new class() extends HashNamer {};
        $resolver = $this->createNamerResolver($namer, ['length' => 8], []);
        $object = self::createObject();
        $configured = $resolver->resolve($object, 'file', ['mapping' => 'configured']);
        $plain = $resolver->resolve($object, 'file', ['mapping' => 'plain']);

        self::assertNotSame($namer, $configured->getNamer());
        self::assertMatchesRegularExpression('/^[[:xdigit:]]{8}\.txt$/', $configured->getNamer()->name($object, $this->createFileMapping()));
        self::assertMatchesRegularExpression('/^[[:xdigit:]]{40}\.txt$/', $plain->getNamer()->name($object, $this->createFileMapping()));
    }

    public function testMappingsWithoutOptionsRetainTheServiceConfigurationAtResolution(): void
    {
        $namer = new SmartUniqueNamer($this->getTransliterator());
        $resolver = $this->createNamerResolver($namer, [], []);
        $object = self::createObject();
        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'plain']);
        $namer->configure(['keep_extension' => true]);

        self::assertStringEndsWith('.txt', $mapping->getNamer()->name($object, $this->createFileMapping('test.php')));
        self::assertStringEndsWith('.php', $namer->name($object, $this->createFileMapping('test.php')));
    }

    /**
     * @param array<string, mixed> $configuredOptions options of the mapping which sets them
     * @param array<string, mixed> $plainOptions      options of the mapping which must not inherit them
     */
    private function createNamerResolver(NamerInterface $namer, array $configuredOptions, array $plainOptions, bool $keepExtension = false): PropertyMappingResolver
    {
        return new PropertyMappingResolver(
            [self::NAMER_SERVICE => $namer],
            [],
            [
                'configured' => self::createMappingConfiguration(['service' => self::NAMER_SERVICE, 'options' => $configuredOptions], self::NO_NAMER, $keepExtension),
                'plain' => self::createMappingConfiguration(['service' => self::NAMER_SERVICE, 'options' => $plainOptions], self::NO_NAMER),
            ]
        );
    }

    /**
     * @param array<string, mixed> $configuredOptions options of the mapping which sets them
     * @param array<string, mixed> $plainOptions      options of the mapping which must not inherit them
     */
    private function createDirectoryNamerResolver(DirectoryNamerInterface $namer, array $configuredOptions, array $plainOptions): PropertyMappingResolver
    {
        return new PropertyMappingResolver(
            [],
            [self::DIRECTORY_NAMER_SERVICE => $namer],
            [
                'configured' => self::createMappingConfiguration(self::NO_NAMER, ['service' => self::DIRECTORY_NAMER_SERVICE, 'options' => $configuredOptions]),
                'plain' => self::createMappingConfiguration(self::NO_NAMER, ['service' => self::DIRECTORY_NAMER_SERVICE, 'options' => $plainOptions]),
            ]
        );
    }

    /**
     * @param array<string, mixed> $namer
     * @param array<string, mixed> $directoryNamer
     *
     * @return array<string, mixed>
     */
    private static function createMappingConfiguration(array $namer, array $directoryNamer, bool $keepExtension = false): array
    {
        return [
            'upload_destination' => '/tmp',
            'uri_prefix' => '/',
            'namer' => $namer,
            'directory_namer' => $directoryNamer,
            'delete_on_remove' => true,
            'erase_fields' => true,
            'delete_on_update' => true,
            'inject_on_load' => false,
            'namer_keep_extension' => $keepExtension,
            'db_driver' => 'orm',
        ];
    }

    private static function createObject(): object
    {
        return new class() {
            public string $title = 'Tîtle Wîth Accents';

            public \DateTimeImmutable $createdAt;

            public function __construct()
            {
                $this->createdAt = new \DateTimeImmutable('2018-09-23 12:34:56');
            }
        };
    }

    /**
     * The mapping the namers read the file and the file name from, which is not the one under
     * test: the resolved mapping is only used for the configuration it applies to the namer.
     */
    private function createFileMapping(string $originalName = 'Fôo Bàr.txt'): PropertyMappingInterface
    {
        $file = $this->getUploadedFileMock();
        $file->method('getClientOriginalName')->willReturn($originalName);
        $file->method('guessExtension')->willReturn('txt');

        $mapping = $this->getPropertyMappingMock();
        $mapping->method('getFile')->willReturn($file);
        $mapping->method('getFileName')->willReturn('0123456789.jpg');

        return $mapping;
    }
}
