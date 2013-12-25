<?php

namespace Vich\UploaderBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register the uploadable models in BazingaPropelEventDispatcherBundle
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class RegisterPropelModelsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->getParameter('vich_uploader.driver') !== 'propel') {
            return;
        }

        $metadata = $container->get('vich_uploader.metadata_reader');
        $classes = $metadata->getUploadableClasses();

        foreach ($container->findTaggedServiceIds('vich_uploader.listener') as $id => $attributes) {
            $listener = $container->getDefinition($id);
            $listener->setClass($container->getDefinition($listener->getParent())->getClass());
            $listener->setPublic(true);

            foreach ($classes as $class) {
                $listener->addTag('propel.event_subscriber', array(
                    'class' => $class
                ));
            }
        }
    }
}
