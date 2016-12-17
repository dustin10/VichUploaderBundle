<?php

namespace Vich\UploaderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Vich\UploaderBundle\Exception\MappingNotFoundException;

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

        $mappings = $container->getParameter('vich_uploader.mappings');
        $hasPropelMapping = false;

        foreach ($mappings as $mapping) {
            if ($mapping['db_driver'] === 'propel') {
                $hasPropelMapping = true;
                break;
            }
        }

        if (!$hasPropelMapping) {
            return;
        }

        $serviceTypes = array(
            'inject', 'clean', 'remove', 'upload',
        );

        $metadata = $container->get('vich_uploader.metadata_reader');

        foreach ($metadata->getUploadableClasses() as $class) {
            foreach ($metadata->getUploadableFields($class) as $field) {
                if (empty($mappings[$field['mapping']])) {
                    throw MappingNotFoundException::createNotFoundForClassAndField($field['mapping'], $class, $field['propertyName']);
                }

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
