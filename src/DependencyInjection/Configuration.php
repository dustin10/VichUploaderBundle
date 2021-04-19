<?php

namespace Vich\UploaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\BaseNode;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    /** @var array<int, string> */
    private $supportedDbDrivers = ['orm', 'mongodb', 'phpcr'];

    /** @var array<int, string> */
    private $supportedStorages = ['gaufrette', 'flysystem', 'file_system'];

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('vich_uploader');
        $root = $builder->getRootNode();
        $this->addGeneralSection($root);
        $this->addMetadataSection($root);
        $this->addMappingsSection($root);

        return $builder;
    }

    private function addGeneralSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->scalarNode('default_filename_attribute_suffix')
                    ->defaultValue('_name')
                ->end()
                ->scalarNode('db_driver')
                    ->isRequired()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static function ($v) {
                            return \strtolower($v);
                        })
                    ->end()
                    ->validate()
                        ->ifNotInArray($this->supportedDbDrivers)
                        ->thenInvalid('The db driver %s is not supported. Please choose one of '.\implode(', ', $this->supportedDbDrivers))
                    ->end()
                ->end()
                ->scalarNode('storage')
                    ->defaultValue('file_system')
                    ->validate()
                        ->ifTrue(function ($storage) {
                            return 0 !== \strpos($storage, '@') && !\in_array($storage, $this->supportedStorages, true);
                        })
                        ->thenInvalid('The storage %s is not supported. Please choose one of '.\implode(', ', $this->supportedStorages).' or provide a service name prefixed with "@".')
                    ->end()
                ->end()
            ->scalarNode('templating')->defaultFalse()->setDeprecated(...$this->getTemplatingDeprecationMessage())->end()
            ->scalarNode('twig')->defaultTrue()->info('twig requires templating')->end()
            ->scalarNode('form')->defaultTrue()->end()
            ->end()
        ;
    }

    private function addMetadataSection(ArrayNodeDefinition $node): void
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

    private function addMappingsSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('mappings')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('uri_prefix')->defaultValue('/uploads')->end()
                            ->scalarNode('upload_destination')->isRequired()->end()
                            ->arrayNode('namer')
                                ->addDefaultsIfNotSet()
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(static function ($v) {
                                        return ['service' => $v, 'options' => []];
                                    })
                                ->end()
                                ->children()
                                ->scalarNode('service')->defaultNull()->end()
                                ->variableNode('options')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->arrayNode('directory_namer')
                                ->addDefaultsIfNotSet()
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(static function ($v) {
                                        return ['service' => $v, 'options' => []];
                                    })
                                ->end()
                                ->children()
                                ->scalarNode('service')->defaultNull()->end()
                                ->variableNode('options')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->scalarNode('delete_on_remove')->defaultTrue()->end()
                            ->scalarNode('delete_on_update')->defaultTrue()->end()
                            ->scalarNode('inject_on_load')->defaultFalse()->end()
                            ->scalarNode('db_driver')
                                ->defaultNull()
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(static function ($v) {
                                        return \strtolower($v);
                                    })
                                ->end()
                                ->validate()
                                    ->ifNotInArray($this->supportedDbDrivers)
                                    ->thenInvalid('The db driver %s is not supported. Please choose one of '.\implode(', ', $this->supportedDbDrivers))
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Keep compatibility with symfony/config < 5.1.
     *
     * The signature of method NodeDefinition::setDeprecated() has been updated to
     * NodeDefinition::setDeprecation(string $package, string $version, string $message).
     */
    private function getTemplatingDeprecationMessage(): array
    {
        $message = 'The "%node%" option is deprecated.';

        if (\method_exists(BaseNode::class, 'getDeprecation')) {
            return ['vich/uploader-bundle', '1.13.2', $message];
        }

        return [$message];
    }
}
