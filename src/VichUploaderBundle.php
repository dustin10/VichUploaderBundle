<?php

namespace Vich\UploaderBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vich\UploaderBundle\DependencyInjection\Compiler\RegisterFlysystemRegistryPass;
use Vich\UploaderBundle\DependencyInjection\Compiler\RegisterMappingDriversPass;
use Vich\UploaderBundle\DependencyInjection\Compiler\RegisterSluggerPass;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 *
 * @internal
 */
final class VichUploaderBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterMappingDriversPass());
        $container->addCompilerPass(new RegisterFlysystemRegistryPass());
        $container->addCompilerPass(new RegisterSluggerPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
