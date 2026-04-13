<?php

namespace Vich\UploaderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class RegisterCleanupCommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('vich_uploader.command.cleanup')) {
            return;
        }

        $managers = [];
        if ($container->hasDefinition('doctrine_mongodb')) {
            $managers[] = new Reference('doctrine_mongodb');
        }
        if ($container->hasDefinition('doctrine')) {
            $managers[] = new Reference('doctrine');
        }
        if ($container->hasDefinition('doctrine_phpcr')) {
            $managers[] = new Reference('doctrine_phpcr');
        }

        if (\count($managers) > 0) {
            $container->getDefinition('vich_uploader.command.cleanup')
                ->replaceArgument(3, $managers);
        }
    }
}
