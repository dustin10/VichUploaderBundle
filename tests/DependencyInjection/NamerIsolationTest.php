<?php

namespace Vich\UploaderBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\DependencyInjection\VichUploaderExtension;
use Vich\UploaderBundle\Mapping\PropertyMappingResolver;
use Vich\UploaderBundle\Naming\ChainDirectoryNamer;
use Vich\UploaderBundle\Tests\Fixtures\IsolatedNamer;
use Vich\UploaderBundle\Tests\Fixtures\IsolatedNamerDecorator;
use Vich\UploaderBundle\Tests\Fixtures\IsolatedNamerFactory;
use Vich\UploaderBundle\Tests\TestCase;

final class NamerIsolationTest extends TestCase
{
    public static function serviceKinds(): iterable
    {
        yield 'regular service' => ['regular'];
        yield 'singleton factory' => ['factory'];
        yield 'decorator' => ['decorator'];
    }

    #[DataProvider('serviceKinds')]
    public function testCustomServicesWithNestedStateRemainIndependent(string $kind): void
    {
        $builder = $this->builder([
            'configured' => ['namer' => ['service' => 'custom', 'options' => ['prefix' => 'configured']]],
            'plain' => ['namer' => 'custom'],
        ]);
        $builder->register('state', \stdClass::class)->setProperty('prefix', 'service-default');
        $definition = $builder->register('custom', IsolatedNamer::class)
            ->setArguments([new Reference('state')])->addTag('vich_uploader.namer')->setPublic(true);
        if ('factory' === $kind) {
            $builder->register('source', IsolatedNamer::class)->setArguments([new Reference('state')]);
            $builder->register('factory', IsolatedNamerFactory::class)->setArguments([new Reference('source')]);
            $definition->setFactory([new Reference('factory'), 'create'])->setArguments([]);
        } elseif ('decorator' === $kind) {
            $builder->register('decorator', IsolatedNamerDecorator::class)
                ->setDecoratedService('custom')->setArguments([new Reference('decorator.inner')])->setPublic(true);
        }
        $container = $this->compile($builder);
        $resolver = $this->resolver($container);
        $object = new \stdClass();
        $plain = $resolver->resolve($object, 'file', ['mapping' => 'plain']);
        $configured = $resolver->resolve($object, 'file', ['mapping' => 'configured']);
        $plainAgain = $resolver->resolve($object, 'file', ['mapping' => 'plain']);
        $prefix = 'decorator' === $kind ? 'decorated-' : '';

        self::assertNotSame($plain->getNamer(), $configured->getNamer());
        self::assertNotSame($plain->getNamer(), $plainAgain->getNamer());
        self::assertSame($prefix.'configured', $configured->getUploadName($object));
        self::assertSame($prefix.'service-default', $plain->getUploadName($object));
        self::assertSame($prefix.'service-default', $plainAgain->getUploadName($object));
        self::assertSame($prefix.'service-default', $container->get('custom')->name($object, $plain));
    }

    public function testLazyServiceConfigurationRemainsIndependent(): void
    {
        $builder = $this->builder([
            'configured' => ['namer' => ['service' => 'hash', 'options' => ['length' => 8]]],
            'plain' => ['namer' => 'hash'],
        ]);
        $builder->register('hash', \Vich\UploaderBundle\Naming\HashNamer::class)
            ->setLazy(true)->addTag('vich_uploader.namer');
        $resolver = $this->resolver($this->compile($builder));
        $object = (object) ['file' => new \Symfony\Component\HttpFoundation\File\UploadedFile(__DIR__.'/../Fixtures/App/app/Resources/images/symfony_black_03.png', 'image.png')];
        $plain = $resolver->resolve($object, 'file', ['mapping' => 'plain']);
        $configured = $resolver->resolve($object, 'file', ['mapping' => 'configured']);

        self::assertNotSame($plain->getNamer(), $configured->getNamer());
        self::assertMatchesRegularExpression('/^[a-f0-9]{8}\\.png$/', $configured->getUploadName($object));
        self::assertMatchesRegularExpression('/^[a-f0-9]{40}\\.png$/', $plain->getUploadName($object));
    }

    public function testFileDirectoryAndNestedChainRolesDoNotShareOptions(): void
    {
        $builder = $this->builder([
            'product.photos' => [
                'namer' => ['service' => 'custom', 'options' => ['prefix' => 'file']],
                'directory_namer' => [
                    'service' => ChainDirectoryNamer::class,
                    'options' => ['namers' => [
                        ['service' => 'custom', 'options' => ['prefix' => 'first']],
                        ['service' => ChainDirectoryNamer::class, 'options' => ['namers' => [
                            ['service' => 'custom', 'options' => ['prefix' => 'second']],
                            ['service' => 'custom'],
                        ], 'separator' => '-']],
                    ]],
                ],
            ],
            'plain' => ['directory_namer' => IsolatedNamer::class],
        ]);
        $builder->register('custom', IsolatedNamer::class)
            ->addTag('vich_uploader.namer')->addTag('vich_uploader.dir_namer');
        $resolver = $this->resolver($this->compile($builder));
        $object = new \stdClass();
        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'product.photos']);
        $plain = $resolver->resolve($object, 'file', ['mapping' => 'plain']);

        self::assertSame('file', $mapping->getUploadName($object));
        self::assertSame('first/second-default', $mapping->getDirectoryNamer()->directoryName($object, $mapping));
        self::assertSame('default', $plain->getDirectoryNamer()->directoryName($object, $plain));
    }

    public function testNonSharedServicesAreStillResolvedOnEveryCall(): void
    {
        $builder = $this->builder(['products' => ['namer' => 'custom']]);
        $builder->register('source', IsolatedNamer::class);
        $builder->register('factory', IsolatedNamerFactory::class)->setArguments([new Reference('source')]);
        $builder->register('custom', IsolatedNamer::class)->setShared(false)
            ->setFactory([new Reference('factory'), 'createNext'])->addTag('vich_uploader.namer');
        $resolver = $this->resolver($this->compile($builder));
        $object = new \stdClass();
        $first = $resolver->resolve($object, 'file', ['mapping' => 'products']);
        $second = $resolver->resolve($object, 'file', ['mapping' => 'products']);

        self::assertSame('1', $first->getUploadName($object));
        self::assertSame('2', $second->getUploadName($object));
    }

    private function builder(array $mappings): ContainerBuilder
    {
        $container = new ContainerBuilder();
        foreach (['kernel.bundles' => [], 'kernel.bundles_metadata' => [], 'kernel.project_dir' => \sys_get_temp_dir(), 'kernel.cache_dir' => \sys_get_temp_dir(), 'kernel.build_dir' => \sys_get_temp_dir(), 'kernel.debug' => true] as $name => $value) {
            $container->setParameter($name, $value);
        }
        foreach ($mappings as &$mapping) {
            $mapping['upload_destination'] = \sys_get_temp_dir();
        }
        unset($mapping);
        (new VichUploaderExtension())->load([['db_driver' => 'orm', 'mappings' => $mappings]], $container);
        $container->register('slugger', AsciiSlugger::class);
        $container->register('property_accessor', PropertyAccessor::class);
        $container->register('event_dispatcher')->setSynthetic(true);
        $container->getDefinition('vich_uploader.property_mapping_resolver')->setPublic(true);

        return $container;
    }

    private function compile(ContainerBuilder $builder): Container
    {
        $builder->compile();
        $class = 'NamerIsolationContainer'.\str_replace('.', '', \uniqid('', true));
        $path = \tempnam(\sys_get_temp_dir(), 'vich_namers_');
        try {
            \file_put_contents($path, (new PhpDumper($builder))->dump(['class' => $class]));
            require $path;

            return new $class();
        } finally {
            \unlink($path);
        }
    }

    private function resolver(Container $container): PropertyMappingResolver
    {
        $resolver = $container->get('vich_uploader.property_mapping_resolver');
        self::assertInstanceOf(PropertyMappingResolver::class, $resolver);

        return $resolver;
    }
}
