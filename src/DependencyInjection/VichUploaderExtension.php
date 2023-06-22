<?php

namespace Vich\UploaderBundle\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Vich\UploaderBundle\Exception\MissingPackageException;
use Vich\UploaderBundle\Metadata\CacheWarmer;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 *
 * @internal
 */
final class VichUploaderExtension extends Extension
{
    protected array $tagMap = [
        'orm' => 'doctrine.event_listener',
        'mongodb' => 'doctrine_mongodb.odm.event_listener',
        'phpcr' => 'doctrine_phpcr.event_listener',
    ];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $config = $this->fixDbDriverConfig($config);
        $config = $this->createNamerServices($container, $config);

        // define a few parameters
        $container->setParameter('vich_uploader.default_filename_attribute_suffix', $config['default_filename_attribute_suffix']);
        $container->setParameter('vich_uploader.mappings', $config['mappings']);

        if (\str_starts_with((string) $config['storage'], '@')) {
            $container->setAlias('vich_uploader.storage', \substr((string) $config['storage'], 1));
        } else {
            $container->setAlias('vich_uploader.storage', 'vich_uploader.storage.'.$config['storage']);
        }
        $container->setAlias(StorageInterface::class, new Alias('vich_uploader.storage', false));

        $this->loadServicesFiles($container, $config);
        $this->registerMetadataDirectories($container, $config);
        $this->registerAnnotationStrategy($container, $config);
        $this->registerCacheStrategy($container, $config);

        $this->registerListeners($container, $config);

        $this->registerFormTheme($container);
    }

    protected function loadServicesFiles(ContainerBuilder $container, array $config): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../config'));

        $toLoad = [
            'adapter.xml', 'listener.xml', 'storage.xml', 'injector.xml',
            'mapping.xml', 'factory.xml', 'namer.xml', 'handler.xml', 'command.xml', 'collector.xml',
        ];
        foreach ($toLoad as $file) {
            $loader->load($file);
        }

        if (\in_array($config['storage'], ['gaufrette', 'flysystem'], true)) {
            $loader->load($config['storage'].'.xml');
        }

        if ($config['form']) {
            $loader->load('form.xml');
        }

        if ($config['twig']) {
            $loader->load('twig.xml');
        }
    }

    protected function registerMetadataDirectories(ContainerBuilder $container, array $config): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        // directories
        $directories = [];
        if ($config['metadata']['auto_detection']) {
            $projectDir = $container->getParameter('kernel.project_dir');
            if (\is_string($projectDir)) {
                $appConfigDirectory = $projectDir.'/config/vich_uploader';
                if (\is_dir($appConfigDirectory)) {
                    $directories['App'] = $appConfigDirectory;
                }
            }

            foreach ($bundles as $class) {
                $ref = new \ReflectionClass($class);
                $directory = \dirname($ref->getFileName()).'/../config/vich_uploader';

                if (!\is_dir($directory)) {
                    continue;
                }

                $directories[$ref->getNamespaceName()] = $directory;
            }
        }

        foreach ($config['metadata']['directories'] as $directory) {
            $directory['path'] = \rtrim(\str_replace('\\', '/', (string) $directory['path']), '/');

            if ('@' === $directory['path'][0]) {
                $bundleName = \substr($directory['path'], 1, \strpos($directory['path'], '/') - 1);

                if (!isset($bundles[$bundleName])) {
                    throw new \RuntimeException(\sprintf('The bundle "%s" has not been registered with Kernel. Available bundles: %s', $bundleName, \implode(', ', \array_keys($bundles))));
                }

                $ref = new \ReflectionClass($bundles[$bundleName]);
                $directory['path'] = \dirname($ref->getFileName()).\substr($directory['path'], \strlen('@'.$bundleName));
            }

            $directories[\rtrim((string) $directory['namespace_prefix'], '\\')] = \rtrim($directory['path'], '\\/');
        }

        $container
            ->getDefinition('vich_uploader.metadata.file_locator')
            ->replaceArgument(0, $directories)
        ;
    }

    protected function registerAnnotationStrategy(ContainerBuilder $container, array $config): void
    {
        if (!$container->has('vich_uploader.metadata_driver.annotation')) {
            return;
        }

        switch ($config['metadata']['type']) {
            case 'annotation':
                if (!class_exists(AnnotationReader::class) || !$container::willBeAvailable('doctrine/annotations', AnnotationReader::class, [])) {
                    $msg = 'Annotations support missing. Try running "composer require doctrine/annotations".';
                    throw new MissingPackageException($msg);
                }

                $container->setDefinition(
                    'vich_uploader.metadata.reader',
                    new Definition(AnnotationReader::class)
                );
                break;

            default:
                $container->setDefinition(
                    'vich_uploader.metadata.reader',
                    $container->getDefinition('vich_uploader.metadata.attribute_reader')
                );
        }
    }

    protected function registerCacheStrategy(ContainerBuilder $container, array $config): void
    {
        if ('none' === $config['metadata']['cache']) {
            $container->removeAlias('vich_uploader.metadata.cache');
        } elseif ('file' === $config['metadata']['cache']) {
            $container
                ->getDefinition('vich_uploader.metadata.cache.file_cache')
                ->replaceArgument(0, $config['metadata']['file_cache']['dir'])
            ;
            $container
                ->getDefinition(CacheWarmer::class)
                ->replaceArgument(0, $config['metadata']['file_cache']['dir'])
            ;

            $dir = $container->getParameterBag()->resolveValue($config['metadata']['file_cache']['dir']);
            if (!\file_exists($dir) && !@\mkdir($dir, 0o777, true)) {
                throw new \RuntimeException(\sprintf('Could not create cache directory "%s".', $dir));
            }
        } else {
            $container->setAlias('vich_uploader.metadata.cache', new Alias($config['metadata']['cache'], false));
        }
    }

    protected function fixDbDriverConfig(array $config): array
    {
        // mapping with no declared db_driver use the top-level one
        foreach ($config['mappings'] as &$mapping) {
            $mapping['db_driver'] = $mapping['db_driver'] ?: $config['db_driver'];
        }

        return $config;
    }

    protected function registerListeners(ContainerBuilder $container, array $config): void
    {
        $servicesMap = [
            'inject_on_load' => ['name' => 'inject', 'priority' => 0, 'events' => ['postLoad']],
            'delete_on_update' => ['name' => 'clean', 'priority' => 50, 'events' => ['preUpdate']],
            'delete_on_remove' => ['name' => 'remove', 'priority' => 0, 'events' => ['preRemove', 'postFlush']],
        ];

        foreach ($config['mappings'] as $name => $mapping) {
            $driver = $mapping['db_driver'];

            // create optional listeners
            foreach ($servicesMap as $configOption => $service) {
                if (!$mapping[$configOption]) {
                    continue;
                }

                $this->createListener($container, $name, $service['name'], $driver, $service['events'], $service['priority']);
            }

            // the upload listener is mandatory
            $this->createListener($container, $name, 'upload', $driver, ['prePersist', 'preUpdate']);
        }
    }

    protected function createNamerServices(ContainerBuilder $container, array $config): array
    {
        foreach ($config['mappings'] as $name => $mapping) {
            if (!empty($mapping['namer']['service'])) {
                $config['mappings'][$name] = $this->createNamerService($container, $name, $mapping);
            }
        }

        return $config;
    }

    protected function createNamerService(ContainerBuilder $container, string $mappingName, array $mapping): array
    {
        $serviceId = \sprintf('%s.%s', $mapping['namer']['service'], $mappingName);
        $container->setDefinition($serviceId, new ChildDefinition($mapping['namer']['service']));

        $mapping['namer']['service'] = $serviceId;

        return $mapping;
    }

    protected function createListener(
        ContainerBuilder $container,
        string $name,
        string $type,
        string $driver,
        array $events,
        int $priority = 0
    ): void {
        $definition = $container
            ->setDefinition(\sprintf('vich_uploader.listener.%s.%s', $type, $name), new ChildDefinition(\sprintf('vich_uploader.listener.%s.%s', $type, $driver)))
            ->replaceArgument(0, $name)
            ->replaceArgument(1, new Reference('vich_uploader.adapter.'.$driver));

        if (isset($this->tagMap[$driver])) {
            foreach ($events as $event) {
                $definition->addTag($this->tagMap[$driver], ['event' => $event, 'priority' => $priority]);
            }
        }
    }

    private function registerFormTheme(ContainerBuilder $container): void
    {
        $resources = $container->hasParameter('twig.form.resources') ?
            $container->getParameter('twig.form.resources') : [];

        \array_unshift($resources, '@VichUploader/Form/fields.html.twig');
        $container->setParameter('twig.form.resources', $resources);
    }
}
