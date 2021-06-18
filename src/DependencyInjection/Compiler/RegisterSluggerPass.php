<?php

namespace Vich\UploaderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @internal
 */
final class RegisterSluggerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('slugger')) {
            $definition = new Definition(AsciiSlugger::class);
            $container->addDefinitions(['slugger' => $definition]);
            $container->addAliases([SluggerInterface::class => 'slugger']);
        }
    }
}
