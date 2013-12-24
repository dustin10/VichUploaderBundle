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

        $propelListener = $container->getDefinition('vich_uploader.listener.uploader.propel');
        $metadata = $container->get('vich_uploader.metadata_reader');
        foreach ($metadata->getUploadableClasses() as $class) {
            $propelListener->addTag('propel.event_subscriber', array(
                'class' => $class
            ));
        }
    }
}
