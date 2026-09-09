<?php

namespace Vich\UploaderBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\DependencyInjection\VichUploaderExtension;
use Vich\UploaderBundle\Mapping\PropertyMappingResolver;
use Vich\UploaderBundle\Naming\PropertyNamer;
use Vich\UploaderBundle\Naming\SmartUniqueNamer;
use Vich\UploaderBundle\Tests\Fixtures\NamerDecorator;
use Vich\UploaderBundle\Tests\Fixtures\NamerFactory;
use Vich\UploaderBundle\Tests\Fixtures\NonCloneableNamer;
use Vich\UploaderBundle\Tests\Fixtures\ThrowingNamer;
use Vich\UploaderBundle\Tests\TestCase;
use Vich\UploaderBundle\Util\Transliterator;

#[AllowMockObjectsWithoutExpectations]
final class NamerIsolationTest extends TestCase
{
    public function testContainerServicesKeepMappingConfigurationAfterOtherResolutions(): void
    {
        $container = $this->createContainer([
            'plain' => ['namer' => SmartUniqueNamer::class],
            'keeping' => ['namer' => SmartUniqueNamer::class, 'namer_keep_extension' => true],
            'explicit' => ['namer' => ['service' => SmartUniqueNamer::class, 'options' => ['keep_extension' => true]]],
        ]);
        $container->getDefinition(SmartUniqueNamer::class)->setPublic(true);
        $resolver = $this->compileResolver($container);
        $object = new \stdClass();
        $plain = $resolver->resolve($object, 'file', ['mapping' => 'plain']);
        $keeping = $resolver->resolve($object, 'file', ['mapping' => 'keeping']);
        $explicit = $resolver->resolve($object, 'file', ['mapping' => 'explicit']);
        $plainAgain = $resolver->resolve($object, 'file', ['mapping' => 'plain']);
        $file = $this->getUploadedFileMock();
        $file->method('getClientOriginalName')->willReturn('test.php');
        $file->method('guessExtension')->willReturn('txt');
        $fileMapping = $this->getPropertyMappingMock();
        $fileMapping->method('getFile')->willReturn($file);

        self::assertNotSame($plain->getNamer(), $keeping->getNamer());
        self::assertNotSame($container->get(SmartUniqueNamer::class), $plain->getNamer());
        self::assertStringEndsWith('.txt', $plain->getNamer()->name($object, $fileMapping));
        self::assertStringEndsWith('.php', $keeping->getNamer()->name($object, $fileMapping));
        self::assertStringEndsWith('.php', $explicit->getNamer()->name($object, $fileMapping));
        self::assertStringEndsWith('.txt', $plainAgain->getNamer()->name($object, $fileMapping));
    }

    public function testCustomServicesRetainTheirIdentityAndConfigurationCalls(): void
    {
        $container = $this->createContainer([
            'first' => ['namer' => ['service' => 'custom', 'options' => ['prefix' => 'first']]],
            'second' => ['namer' => ['service' => 'custom', 'options' => ['prefix' => 'second']]],
        ]);
        $container->register('custom', NonCloneableNamer::class)->addTag('vich_uploader.namer');
        $resolver = $this->compileResolver($container);
        $object = new \stdClass();
        $first = $resolver->resolve($object, 'file', ['mapping' => 'first']);
        $second = $resolver->resolve($object, 'file', ['mapping' => 'second']);
        $firstAgain = $resolver->resolve($object, 'file', ['mapping' => 'first']);

        self::assertSame($first->getNamer(), $second->getNamer());
        self::assertSame($first->getNamer(), $firstAgain->getNamer());
        $namer = $first->getNamer();
        self::assertInstanceOf(NonCloneableNamer::class, $namer);
        self::assertSame(3, $namer->configurationCalls);
        self::assertSame('first', $firstAgain->getUploadName($object));
    }

    public function testNonSharedCustomServicesAreResolvedAgain(): void
    {
        $container = $this->createContainer(['products' => ['namer' => ['service' => 'custom', 'options' => ['prefix' => 'product']]]]);
        $container->register('custom', NonCloneableNamer::class)->addTag('vich_uploader.namer')->setShared(false);
        $resolver = $this->compileResolver($container);
        $object = new \stdClass();
        $first = $resolver->resolve($object, 'file', ['mapping' => 'products']);
        $second = $resolver->resolve($object, 'file', ['mapping' => 'products']);

        self::assertNotSame($first->getNamer(), $second->getNamer());
        self::assertSame('product', $first->getUploadName($object));
        self::assertSame('product', $second->getUploadName($object));
    }

    public function testLazyCustomServicesAreNotCloned(): void
    {
        $container = $this->createContainer(['products' => ['namer' => ['service' => 'custom', 'options' => ['prefix' => 'lazy']]]]);
        $container->register('custom', NonCloneableNamer::class)->addTag('vich_uploader.namer')->setLazy(true);
        $resolver = $this->compileResolver($container);
        $object = new \stdClass();
        $first = $resolver->resolve($object, 'file', ['mapping' => 'products']);
        $second = $resolver->resolve($object, 'file', ['mapping' => 'products']);

        self::assertSame($first->getNamer(), $second->getNamer());
        self::assertSame('lazy', $second->getUploadName($object));
    }

    public function testBuiltInNamersWithDottedMappingsKeepTheirOptions(): void
    {
        $container = $this->createContainer([
            'plain.photos' => ['namer' => SmartUniqueNamer::class],
            'keeping' => ['namer' => SmartUniqueNamer::class, 'namer_keep_extension' => true],
        ]);
        $resolver = $this->compileResolver($container);
        $object = new \stdClass();
        $plain = $resolver->resolve($object, 'file', ['mapping' => 'plain.photos']);
        $keeping = $resolver->resolve($object, 'file', ['mapping' => 'keeping']);
        $file = $this->getUploadedFileMock();
        $file->method('getClientOriginalName')->willReturn('test.php');
        $file->method('guessExtension')->willReturn('txt');
        $fileMapping = $this->getPropertyMappingMock();
        $fileMapping->method('getFile')->willReturn($file);

        self::assertStringEndsWith('.txt', $plain->getNamer()->name($object, $fileMapping));
        self::assertStringEndsWith('.php', $keeping->getNamer()->name($object, $fileMapping));
    }

    public function testCustomFactoryResultsAreNotReplaced(): void
    {
        $container = $this->createContainer([
            'first' => ['namer' => ['service' => 'custom', 'options' => ['prefix' => 'first']]],
            'second' => ['namer' => ['service' => 'custom', 'options' => ['prefix' => 'second']]],
        ]);
        $container->register('singleton', NonCloneableNamer::class);
        $container->register('factory', NamerFactory::class)->setArguments([new Reference('singleton')]);
        $container->register('custom', NonCloneableNamer::class)
            ->setFactory([new Reference('factory'), 'create'])->addTag('vich_uploader.namer');
        $resolver = $this->compileResolver($container);
        $object = new \stdClass();
        $first = $resolver->resolve($object, 'file', ['mapping' => 'first']);
        $second = $resolver->resolve($object, 'file', ['mapping' => 'second']);

        self::assertSame($first->getNamer(), $second->getNamer());
        self::assertSame('second', $second->getUploadName($object));
    }

    public function testDecoratorsArePreserved(): void
    {
        $container = $this->createContainer(['products' => ['namer' => ['service' => 'custom', 'options' => ['prefix' => 'product']]]]);
        $container->register('custom', NonCloneableNamer::class)->addTag('vich_uploader.namer');
        $container->register('decorator', NamerDecorator::class)
            ->setDecoratedService('custom')->setArguments([new Reference('decorator.inner')]);
        $resolver = $this->compileResolver($container);
        $object = new \stdClass();
        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'products']);

        self::assertSame('decorated-product', $mapping->getUploadName($object));
    }

    public function testDirectoryNamerClassMatchesAnExistingCustomService(): void
    {
        $container = $this->createContainer(['products' => ['directory_namer' => ['service' => NonCloneableNamer::class, 'options' => ['prefix' => 'directory']]]]);
        $container->register('custom', NonCloneableNamer::class)->addTag('vich_uploader.dir_namer');
        $resolver = $this->compileResolver($container);
        $object = new \stdClass();
        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'products']);

        self::assertSame('directory', $mapping->getDirectoryNamer()->directoryName($object, $mapping));
    }

    public function testMappingNamesDoNotCreateDirectoryServiceCollisions(): void
    {
        $container = $this->createContainer([
            'directory.photos' => ['namer' => ['service' => 'custom', 'options' => ['prefix' => 'file']]],
            'photos' => ['directory_namer' => 'custom'],
        ]);
        $container->register('custom', NonCloneableNamer::class)
            ->addTag('vich_uploader.namer')->addTag('vich_uploader.dir_namer');
        $resolver = $this->compileResolver($container);
        $object = new \stdClass();
        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'directory.photos']);

        self::assertSame('file', $mapping->getUploadName($object));
        self::assertSame($mapping->getNamer(), $resolver->resolve($object, 'file', ['mapping' => 'photos'])->getDirectoryNamer());
    }

    public function testResolvingABuiltInNamerDoesNotInstantiateLaterServices(): void
    {
        $container = new ContainerBuilder();
        $container->register(SmartUniqueNamer::class);
        $container->register('unrelated', ThrowingNamer::class)->addTag('vich_uploader.namer');
        $container = $this->createContainer(['products' => ['namer' => SmartUniqueNamer::class]], $container);
        $resolver = $this->compileResolver($container);
        $mapping = $resolver->resolve(new \stdClass(), 'file', ['mapping' => 'products']);

        self::assertInstanceOf(SmartUniqueNamer::class, $mapping->getNamer());
    }

    public function testPropertyNamerConfiguredByServiceCallsDoesNotReceiveIncompleteOptions(): void
    {
        $container = $this->createContainer(['plain' => ['namer' => 'configured_property']]);
        $container->register('configured_property', PropertyNamer::class)
            ->setArguments([new Reference(Transliterator::class)])
            ->addTag('vich_uploader.namer')
            ->addMethodCall('configure', [['property' => 'title']]);
        $resolver = $this->compileResolver($container);
        $object = (object) ['title' => 'title'];
        $mapping = $resolver->resolve($object, 'file', ['mapping' => 'plain']);
        $file = $this->getUploadedFileMock();
        $file->method('getClientOriginalName')->willReturn('title.txt');
        $file->method('guessExtension')->willReturn('txt');
        $fileMapping = $this->getPropertyMappingMock();
        $fileMapping->method('getFile')->willReturn($file);

        self::assertSame('title.txt', $mapping->getNamer()->name($object, $fileMapping));
    }

    public function testAnUnknownDirectoryNamerStillFailsAtResolution(): void
    {
        $container = $this->createContainer(['products' => ['directory_namer' => 'missing']]);
        $resolver = $this->compileResolver($container);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Directory namer service "missing" not found.');

        $resolver->resolve(new \stdClass(), 'file', ['mapping' => 'products']);
    }

    private function createContainer(array $mappings, ?ContainerBuilder $container = null): ContainerBuilder
    {
        $container ??= new ContainerBuilder();
        // Keep the base service before its children in the tagged iterator.
        $container->register(SmartUniqueNamer::class);
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

    private function compileResolver(ContainerBuilder $container): PropertyMappingResolver
    {
        $container->compile();

        // Non-shared services behave differently when fetched from ContainerBuilder directly.
        // Exercise the generated container used by applications.
        $class = 'NamerIsolationContainer'.\str_replace('.', '', \uniqid('', true));
        $path = \tempnam(\sys_get_temp_dir(), 'vich_namers_');
        try {
            \file_put_contents($path, (new PhpDumper($container))->dump(['class' => $class]));
            require $path;

            return (new $class())->get('vich_uploader.property_mapping_resolver');
        } finally {
            \unlink($path);
        }
    }
}
