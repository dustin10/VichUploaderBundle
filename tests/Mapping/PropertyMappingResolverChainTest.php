<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use PHPUnit\Framework\Attributes\DataProvider;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingResolver;
use Vich\UploaderBundle\Naming\ChainDirectoryNamer;
use Vich\UploaderBundle\Naming\ConfigurableDirectoryNamer;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\ImmutableConfigurableInterface;
use Vich\UploaderBundle\Tests\Fixtures\IsolatedNamer;
use Vich\UploaderBundle\Tests\TestCase;

final class PropertyMappingResolverChainTest extends TestCase
{
    public function testRepeatedServicesHaveIndependentOptionsInsideAChain(): void
    {
        $shared = new IsolatedNamer();
        $resolver = $this->resolver([
            'chain_mapping' => self::chain([
                ['service' => 'custom', 'options' => ['prefix' => 'first']],
                ['service' => 'custom', 'options' => ['prefix' => 'second']],
            ]),
            'plain' => ['service' => 'custom'],
        ], ['custom' => $shared]);
        $mapping = $resolver->resolve(new \stdClass(), 'file', ['mapping' => 'chain_mapping']);
        $plain = $resolver->resolve(new \stdClass(), 'file', ['mapping' => 'plain']);

        self::assertSame('first/second', self::directory($mapping));
        self::assertSame('default', self::directory($plain));
        self::assertSame('default', $shared->directoryName(new \stdClass(), $mapping));
    }

    public static function resolutionOrders(): iterable
    {
        yield 'configured first' => [false];
        yield 'plain first' => [true];
    }

    #[DataProvider('resolutionOrders')]
    public function testChainsKeepTheirChildrenAndSeparatorsAcrossMappings(bool $reverse): void
    {
        $resolver = $this->resolver([
            'first' => self::chain([self::directoryConfig('one'), self::directoryConfig('two')], '-'),
            'second' => self::chain([self::directoryConfig('three'), self::directoryConfig('four')]),
            'empty' => ['service' => 'chain'],
        ]);
        $mappings = [];
        foreach ($reverse ? ['empty', 'second', 'first'] : ['first', 'second', 'empty'] as $name) {
            $mappings[$name] = $resolver->resolve(new \stdClass(), 'file', ['mapping' => $name]);
        }
        $again = $resolver->resolve(new \stdClass(), 'file', ['mapping' => 'first']);

        self::assertSame('one-two', self::directory($mappings['first']));
        self::assertSame('three/four', self::directory($mappings['second']));
        self::assertSame('', self::directory($mappings['empty']));
        self::assertSame('one-two', self::directory($again));
        self::assertNotSame($mappings['first']->getDirectoryNamer(), $again->getDirectoryNamer());
    }

    public function testNestedChainsResolveRecursivelyEvenWhenTheyUseTheSameService(): void
    {
        $resolver = $this->resolver([
            'nested' => self::chain([
                self::directoryConfig('root'),
                self::chain([self::directoryConfig('year'), self::directoryConfig('month')], '_'),
            ]),
        ]);
        $mapping = $resolver->resolve(new \stdClass(), 'file', ['mapping' => 'nested']);

        self::assertSame('root/year_month', self::directory($mapping));
    }

    public function testPresetChildrenAreCopiedAndAnExplicitEmptyListClearsThem(): void
    {
        $child = new IsolatedNamer();
        $child->configure(['prefix' => 'preset']);
        $chain = new ChainDirectoryNamer();
        $chain->setNamers([$child]);
        $resolver = $this->resolver([
            'defaults' => ['service' => 'chain'],
            'empty' => self::chain([]),
        ], ['chain' => $chain]);
        $defaults = $resolver->resolve(new \stdClass(), 'file', ['mapping' => 'defaults']);
        $empty = $resolver->resolve(new \stdClass(), 'file', ['mapping' => 'empty']);
        $child->configure(['prefix' => 'changed']);

        self::assertSame('preset', self::directory($defaults));
        self::assertSame('', self::directory($empty));
        self::assertSame('changed', $chain->directoryName(new \stdClass(), $defaults));
    }

    public function testOptionsOnANonConfigurableNestedNamerAreRejected(): void
    {
        $plain = new class() implements DirectoryNamerInterface {
            public function directoryName(object|array $object, PropertyMappingInterface $mapping): string
            {
                return 'plain';
            }
        };
        $resolver = $this->resolver([
            'invalid' => self::chain([['service' => 'plain', 'options' => ['prefix' => 'ignored']]]),
        ], ['plain' => $plain]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Namer plain can not receive options');
        $resolver->resolve(new \stdClass(), 'file', ['mapping' => 'invalid']);
    }

    public function testReturningTheSharedInstanceViolatesTheContract(): void
    {
        $broken = new class() implements DirectoryNamerInterface, ImmutableConfigurableInterface {
            public function withOptions(array $options): static
            {
                return $this;
            }

            public function directoryName(object|array $object, PropertyMappingInterface $mapping): string
            {
                return 'shared';
            }
        };
        $resolver = $this->resolver(['invalid' => self::chain([['service' => 'broken']])], ['broken' => $broken]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Namer "broken" must return a new instance from withOptions().');
        $resolver->resolve(new \stdClass(), 'file', ['mapping' => 'invalid']);
    }

    public static function malformedChildren(): iterable
    {
        yield 'not an array' => ['invalid'];
        yield 'missing service' => [[['options' => []]]];
        yield 'invalid service' => [[['service' => 123]]];
    }

    #[DataProvider('malformedChildren')]
    public function testMalformedChainConfigurationFailsClearly(mixed $children): void
    {
        $resolver = $this->resolver(['invalid' => ['service' => 'chain', 'options' => ['namers' => $children]]]);
        $this->expectException(\InvalidArgumentException::class);
        $resolver->resolve(new \stdClass(), 'file', ['mapping' => 'invalid']);
    }

    private static function chain(array $namers, ?string $separator = null): array
    {
        $options = ['namers' => $namers];
        if (null !== $separator) {
            $options['separator'] = $separator;
        }

        return ['service' => 'chain', 'options' => $options];
    }

    private static function directoryConfig(string $path): array
    {
        return ['service' => 'directory', 'options' => ['directory_path' => $path]];
    }

    private function resolver(array $configurations, array $services = []): PropertyMappingResolver
    {
        $mappings = [];
        foreach ($configurations as $name => $configuration) {
            $mappings[$name] = ['upload_destination' => '/tmp', 'directory_namer' => $configuration];
        }

        return new PropertyMappingResolver([], $services + [
            'chain' => new ChainDirectoryNamer(),
            'directory' => new ConfigurableDirectoryNamer(),
        ], $mappings);
    }

    private static function directory(PropertyMappingInterface $mapping): ?string
    {
        return $mapping->getUploadDir(new \stdClass());
    }
}
