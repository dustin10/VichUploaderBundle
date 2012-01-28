<?php

namespace Vich\UploaderBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Vich\UploaderBundle\DependencyInjection\Configuration;

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
        'mongodb' => 'doctrine.common.event_subscriber'
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
     * @param array $configs The configuration
     * @param ContainerBuilder $container The container builder
     */
    public function load(array $configs, ContainerBuilder $container)
    {
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
            'templating.xml', 'driver.xml', 'factory.xml'
        );
        foreach ($toLoad as $file) {
            $loader->load($file);
        }
        
        if ($config['twig']) {
            $loader->load('twig.xml');
        }
        
        $mappings = isset($config['mappings']) ? $config['mappings'] : array();
        $container->setParameter('vich_uploader.mappings', $mappings);
        
        $container->setParameter('vich_uploader.web_dir_name', $config['web_dir_name']);
        $container->setParameter('vich_uploader.adapter.class', $this->adapterMap[$driver]);
        $container->getDefinition('vich_uploader.listener.uploader')->addTag($this->tagMap[$driver]);
    }
}
