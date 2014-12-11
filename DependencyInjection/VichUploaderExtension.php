<?php

namespace Vich\UploaderBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Kernel;

/**
 * VichUploaderExtension.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class VichUploaderExtension extends Extension
{
    /**
     * @var array $tagMap
     */
    protected $tagMap = array(
        'orm'       => 'doctrine.event_subscriber',
        'mongodb'   => 'doctrine_mongodb.odm.event_subscriber',
        'phpcr'     => 'doctrine_phpcr.event_subscriber'
    );

    /**
     * Loads the extension.
     *
     * @param array            $configs   The configuration
     * @param ContainerBuilder $container The container builder
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $config = $this->fixDbDriverConfig($config);

        // define a few parameters
        $container->setParameter('vich_uploader.default_filename_attribute_suffix', $config['default_filename_attribute_suffix']);
        $container->setParameter('vich_uploader.mappings', $config['mappings']);
        $container->setAlias('vich_uploader.storage', 'vich_uploader.storage.' . $config['storage']);

        $this->loadServicesFiles($container, $config);
        $this->registerMetadataDirectories($container, $config);
        $this->registerCacheStrategy($container, $config);

        $this->registerListeners($container, $config);
    }

    protected function loadServicesFiles(ContainerBuilder $container, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $toLoad = array(
            'adapter.xml', 'listener.xml', 'storage.xml', 'injector.xml',
            'templating.xml', 'mapping.xml', 'factory.xml', 'namer.xml',
            'form.xml',
        );
        foreach ($toLoad as $file) {
            $loader->load($file);
        }

        if (in_array($config['storage'], array('gaufrette', 'flysystem'))) {
            $loader->load($config['storage'] . '.xml');
        }

        if ($config['twig']) {
            $loader->load('twig.xml');
        }
    }

    protected function registerMetadataDirectories(ContainerBuilder $container, array $config)
    {
        $bundles = $container->getParameter('kernel.bundles');

        // directories
        $directories = array();
        if ($config['metadata']['auto_detection']) {
            foreach ($bundles as $class) {
                $ref = new \ReflectionClass($class);
                $directory = dirname($ref->getFileName()).'/Resources/config/vich_uploader';

                if (!is_dir($directory)) {
                    continue;
                }

                $directories[$ref->getNamespaceName()] = $directory;
            }
        }

        foreach ($config['metadata']['directories'] as $directory) {
            $directory['path'] = rtrim(str_replace('\\', '/', $directory['path']), '/');

            if ('@' === $directory['path'][0]) {
                $bundleName = substr($directory['path'], 1, strpos($directory['path'], '/') - 1);

                if (!isset($bundles[$bundleName])) {
                    throw new \RuntimeException(sprintf('The bundle "%s" has not been registered with AppKernel. Available bundles: %s', $bundleName, implode(', ', array_keys($bundles))));
                }

                $ref = new \ReflectionClass($bundles[$bundleName]);
                $directory['path'] = dirname($ref->getFileName()).substr($directory['path'], strlen('@'.$bundleName));
            }

            $directories[rtrim($directory['namespace_prefix'], '\\')] = rtrim($directory['path'], '\\/');
        }

        $container
            ->getDefinition('vich_uploader.metadata.file_locator')
            ->replaceArgument(0, $directories)
        ;
    }

    protected function registerCacheStrategy(ContainerBuilder $container, array $config)
    {
        if ('none' === $config['metadata']['cache']) {
            $container->removeAlias('vich_uploader.metadata.cache');
        } elseif ('file' === $config['metadata']['cache']) {
            $container
                ->getDefinition('vich_uploader.metadata.cache.file_cache')
                ->replaceArgument(0, $config['metadata']['file_cache']['dir'])
            ;

            $dir = $container->getParameterBag()->resolveValue($config['metadata']['file_cache']['dir']);
            if (!file_exists($dir) && !$rs = @mkdir($dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
            }
        } else {
            $container->setAlias('vich_uploader.metadata.cache', new Alias($config['metadata']['cache'], false));
        }
    }

    protected function fixDbDriverConfig(array $config)
    {
        // mapping with no declared db_driver use the top-level one
        foreach ($config['mappings'] as &$mapping) {
            $mapping['db_driver'] = $mapping['db_driver'] ?: $config['db_driver'];
        }

        return $config;
    }

    protected function registerListeners(ContainerBuilder $container, array $config)
    {
        $servicesMap = array(
            'inject_on_load'    => array('name' => 'inject', 'priority' => 0),
            'delete_on_update'  => array('name' => 'clean', 'priority' => 50),
            'delete_on_remove'  => array('name' => 'remove', 'priority' => 0)
        );

        foreach ($config['mappings'] as $name => $mapping) {
            $driver = $mapping['db_driver'];

            // create optionnal listeners
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

    protected function createListener(ContainerBuilder $container, $name, $type, $driver, $priority = 0)
    {
        $definition = $container
            ->setDefinition(sprintf('vich_uploader.listener.%s.%s', $type, $name), new DefinitionDecorator(sprintf('vich_uploader.listener.%s.%s', $type, $driver)))
            ->replaceArgument(0, $name)
            ->replaceArgument(1, new Reference('vich_uploader.adapter.'.$driver));

        // propel does not require tags to work
        if (isset($this->tagMap[$driver])) {
            $definition->addTag($this->tagMap[$driver], array('priority' => $priority));
        }
    }
}
