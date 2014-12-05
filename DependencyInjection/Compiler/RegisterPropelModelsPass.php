<?php

namespace Vich\UploaderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register the uploadable models in BazingaPropelEventDispatcherBundle
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class RegisterPropelModelsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('vich_uploader.mappings')) {
            return;
        }

        $serviceTypes = array(
            'inject', 'clean', 'remove', 'upload',
        );

        $metadata = $container->get('vich_uploader.metadata_reader');
        $mappings = $container->getParameter('vich_uploader.mappings');

        foreach ($metadata->getUploadableClasses() as $class) {
            foreach ($metadata->getUploadableFields($class) as $field) {
                $mapping = $mappings[$field['mapping']];

                if ($mapping['db_driver'] !== 'propel') {
                    continue;
                }

                foreach ($serviceTypes as $type) {
                    if (!$container->has(sprintf('vich_uploader.listener.%s.%s', $type, $field['mapping']))) {
                        continue;
                    }

                    $definition = $container->getDefinition(sprintf('vich_uploader.listener.%s.%s', $type, $field['mapping']));
                    $definition->setClass($container->getDefinition($definition->getParent())->getClass());
                    $definition->setPublic(true);
                    $definition->addTag('propel.event_subscriber', array('class' => $class));
                }
            }
        }
    }
}
