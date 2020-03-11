<?php

namespace Vich\UploaderBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class VichUploaderExtension extends Extension
{
    /**
     * @var array
     */
    protected $tagMap = [
        'orm' => 'doctrine.event_subscriber',
        'mongodb' => 'doctrine_mongodb.odm.event_subscriber',
        'phpcr' => 'doctrine_phpcr.event_subscriber',
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

        if (0 === \strpos($config['storage'], '@')) {
            $container->setAlias('vich_uploader.storage', \substr($config['storage'], 1));
        } else {
            $container->setAlias('vich_uploader.storage', 'vich_uploader.storage.'.$config['storage']);
        }
        $container->setAlias(StorageInterface::class, new Alias('vich_uploader.storage', false));

        $this->loadServicesFiles($container, $config);
        $this->registerMetadataDirectories($container, $config);
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
            foreach ($bundles as $class) {
                $ref = new \ReflectionClass($class);
                $directory = \dirname($ref->getFileName()).'/../config/vich_uploader';

                if (!\is_dir($directory)) {
                    $directory = \dirname($ref->getFileName()).'/Resources/config/vich_uploader';
                    if (\is_dir($directory)) {
                        @\trigger_error('Using Resources/config/vich_uploader for auto config discovery is deprecated use ../config/vich_uploader, relative to the Bundle class, instead', E_USER_DEPRECATED);
                    }
                }

                if (!\is_dir($directory)) {
                    continue;
                }

                $directories[$ref->getNamespaceName()] = $directory;
            }
        }

        foreach ($config['metadata']['directories'] as $directory) {
            $directory['path'] = \rtrim(\str_replace('\\', '/', $directory['path']), '/');

            if ('@' === $directory['path'][0]) {
                $bundleName = \substr($directory['path'], 1, \strpos($directory['path'], '/') - 1);

                if (!isset($bundles[$bundleName])) {
                    throw new \RuntimeException(\sprintf('The bundle "%s" has not been registered with AppKernel. Available bundles: %s', $bundleName, \implode(', ', \array_keys($bundles))));
                }

                $ref = new \ReflectionClass($bundles[$bundleName]);
                $directory['path'] = \dirname($ref->getFileName()).\substr($directory['path'], \strlen('@'.$bundleName));
            }

            $directories[\rtrim($directory['namespace_prefix'], '\\')] = \rtrim($directory['path'], '\\/');
        }

        $container
            ->getDefinition('vich_uploader.metadata.file_locator')
            ->replaceArgument(0, $directories)
        ;
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

            $dir = $container->getParameterBag()->resolveValue($config['metadata']['file_cache']['dir']);
            if (!\file_exists($dir) && !@\mkdir($dir, 0777, true)) {
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
            'inject_on_load' => ['name' => 'inject', 'priority' => 0],
            'delete_on_update' => ['name' => 'clean', 'priority' => 50],
            'delete_on_remove' => ['name' => 'remove', 'priority' => 0],
        ];

        foreach ($config['mappings'] as $name => $mapping) {
            $driver = $mapping['db_driver'];

            // create optional listeners
            foreach ($servicesMap as $configOption => $service) {
                if (!$mapping[$configOption]) {
                    continue;
                }

                $this->createListener($container, $name, $service['name'], $driver, $service['priority']);
            }

            // the upload listener is mandatory
            $this->createListener($container, $name, 'upload', $driver);
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
        int $priority = 0
    ): void {
        $definition = $container
            ->setDefinition(\sprintf('vich_uploader.listener.%s.%s', $type, $name), new ChildDefinition(\sprintf('vich_uploader.listener.%s.%s', $type, $driver)))
            ->replaceArgument(0, $name)
            ->replaceArgument(1, new Reference('vich_uploader.adapter.'.$driver));

        if (isset($this->tagMap[$driver])) {
            $definition->addTag($this->tagMap[$driver], ['priority' => $priority]);
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
