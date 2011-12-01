<?php

namespace Vich\UploaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration.
 * 
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Gets the configuration tree builder for the extension.
     * 
     * @return Tree The configuration tree builder
     */
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();
        $root = $tb->root('vich_uploader');
        
        $root
            ->children()
                ->scalarNode('db_driver')->isRequired()->end()
                ->scalarNode('namer')->defaultValue('vich_uploader.namer.default')->end()
                ->arrayNode('mappings')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('upload_dir')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        
        return $tb;
    }
}