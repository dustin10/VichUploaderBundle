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
class VichUploaderBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterMappingDriversPass());
        $container->addCompilerPass(new RegisterPropelModelsPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }
}
