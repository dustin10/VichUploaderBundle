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
                ->scalarNode('storage')->defaultValue('vich_uploader.storage.file_system')->end()
                ->scalarNode('twig')->defaultTrue()->end()
                ->scalarNode('gaufrette')->defaultFalse()->end()
                ->arrayNode('mappings')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('uri_prefix')->defaultValue('/uploads')->end()
                            ->scalarNode('upload_destination')->isRequired()->end()
                            ->scalarNode('namer')->defaultNull()->end()
                            ->scalarNode('directory_namer')->defaultNull()->end()
                            ->scalarNode('delete_on_remove')->defaultTrue()->end()
                            ->scalarNode('delete_on_update')->defaultTrue()->end()
                            ->scalarNode('inject_on_load')->defaultTrue()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tb;
    }
}
