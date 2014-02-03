<?php

namespace Vich\UploaderBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Vich\UploaderBundle\DependencyInjection\Compiler\RegisterPropelModelsPass;

/**
 * VichUploaderBundle.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class VichUploaderBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterPropelModelsPass());
    }
}
