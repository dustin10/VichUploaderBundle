<?php

namespace Vich\UploaderBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vich\UploaderBundle\DependencyInjection\Compiler\RegisterMappingDriversPass;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class VichUploaderBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterMappingDriversPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
