<?php

namespace Vich\UploaderBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vich\UploaderBundle\DependencyInjection\Compiler\RegisterMappingDriversPass;
use Vich\UploaderBundle\DependencyInjection\Compiler\RegisterPropelModelsPass;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class VichUploaderBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterMappingDriversPass());
        $container->addCompilerPass(new RegisterPropelModelsPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
