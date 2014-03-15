<?php

namespace Vich\UploaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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

        $this->addGeneralSection($root);
        $this->addMetadataSection($root);
        $this->addMappingsSection($root);

        return $tb;
    }

    protected function addGeneralSection(ArrayNodeDefinition $node)
    {
        $supportedDbDrivers = array('orm', 'mongodb', 'propel');

        $node
            ->children()
                ->scalarNode('db_driver')
                    ->isRequired()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) { return strtolower($v); })
                    ->end()
                    ->validate()
                        ->ifNotInArray($supportedDbDrivers)
                        ->thenInvalid('The db driver %s is not supported. Please choose one of ' . implode(', ', $supportedDbDrivers))
                    ->end()
                ->end()
                ->scalarNode('storage')->defaultValue('vich_uploader.storage.file_system')->end()
                ->scalarNode('twig')->defaultTrue()->end()
                ->scalarNode('gaufrette')->defaultFalse()->end()
                ->scalarNode('flysystem')->defaultFalse()->end()
            ->end()
        ;
    }

    protected function addMetadataSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('metadata')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('directory', 'directories')
                    ->children()
                        ->scalarNode('cache')->defaultValue('file')->end()
                        ->arrayNode('file_cache')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('dir')->defaultValue('%kernel.cache_dir%/vich_uploader')->end()
                            ->end()
                        ->end()
                        ->booleanNode('auto_detection')->defaultTrue()->end()
                        ->arrayNode('directories')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('path')->isRequired()->end()
                                    ->scalarNode('namespace_prefix')->defaultValue('')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    protected function addMappingsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
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
                            ->scalarNode('inject_on_load')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
