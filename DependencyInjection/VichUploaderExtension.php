<?php

namespace Vich\UploaderBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        'mongodb'   => 'doctrine_mongodb.odm.event_subscriber'
    );

    /**
     * Loads the extension.
     *
     * @param array            $configs   The configuration
     * @param ContainerBuilder $container The container builder
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Set correct doctrine subscriber event for versions of symfony before 2.1
        if (!defined('Symfony\Component\HttpKernel\Kernel::VERSION_ID') || Kernel::VERSION_ID < 20100) {
            $this->tagMap['mongodb'] = 'doctrine.odm.mongodb.event_subscriber';
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->loadServicesFiles($container, $config);
        $this->registerMetadataDirectories($container, $config);
        $this->registerCacheStrategy($container, $config);

        // define a few parameters
        $container->setParameter('vich_uploader.mappings', $config['mappings']);
        $container->setParameter('vich_uploader.driver', $config['db_driver']);
        $container->setParameter('vich_uploader.storage_service', $config['storage']);

        // for propel, the uploader listener registration is handled by a
        // compiler pass
        if ($config['db_driver'] !== 'propel') {
            $container
                ->getDefinition('vich_uploader.listener.uploader.'.$config['db_driver'])
                ->addTag($this->tagMap[$config['db_driver']]);
        }

        $container->setAlias('vich_uploader.adapter', 'vich_uploader.adapter.'.$config['db_driver']);
    }

    protected function loadServicesFiles(ContainerBuilder $container, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $toLoad = array(
            'adapter.xml', 'listener.xml', 'storage.xml', 'injector.xml',
            'templating.xml', 'mapping.xml', 'factory.xml', 'namer.xml'
        );
        foreach ($toLoad as $file) {
            $loader->load($file);
        }

        if ($config['gaufrette']) {
            $loader->load('gaufrette.xml');
        }

        if ($config['flysystem']) {
            $loader->load('flysystem.xml');
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
            foreach ($bundles as $name => $class) {
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
}
