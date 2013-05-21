<?php

namespace Vich\UploaderBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Vich\UploaderBundle\DependencyInjection\Configuration;
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
        'orm' => 'doctrine.event_subscriber',
        'mongodb' => 'doctrine_mongodb.odm.event_subscriber'
    );

    /**
     * @var array $adapterMap
     */
    protected $adapterMap = array(
        'orm' => 'Vich\UploaderBundle\Adapter\ORM\DoctrineORMAdapter',
        'mongodb' => 'Vich\UploaderBundle\Adapter\ODM\MongoDB\MongoDBAdapter'
    );

    /**
     * Loads the extension.
     *
     * @param array $configs   The configuration
     * @param ContainerBuilder $container The container builder
     * @throws \InvalidArgumentException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Set correct doctrine subscriber event for versions of symfony before 2.1
        if (!defined('Symfony\Component\HttpKernel\Kernel::VERSION_ID') || Kernel::VERSION_ID < 20100) {
            $this->tagMap['mongodb'] = 'doctrine.odm.mongodb.event_subscriber';
        }

        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $driver = strtolower($config['db_driver']);
        if (!in_array($driver, array_keys($this->tagMap))) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid "db_driver" configuration option specified: "%s"',
                    $driver
                )
            );
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $toLoad = array(
            'adapter.xml', 'listener.xml', 'storage.xml', 'injector.xml',
            'templating.xml', 'driver.xml', 'factory.xml', 'namer.xml'
        );
        foreach ($toLoad as $file) {
            $loader->load($file);
        }

        if ($config['gaufrette']) {
            $loader->load('gaufrette.xml');
        }

        if ($config['twig']) {
            $loader->load('twig.xml');
        }

        $mappings = isset($config['mappings']) ? $config['mappings'] : array();
        $container->setParameter('vich_uploader.mappings', $mappings);

        $container->setParameter('vich_uploader.storage_service', $config['storage']);
        $container->setParameter('vich_uploader.adapter.class', $this->adapterMap[$driver]);
        $container->getDefinition('vich_uploader.listener.uploader')->addTag($this->tagMap[$driver]);
    }
}
