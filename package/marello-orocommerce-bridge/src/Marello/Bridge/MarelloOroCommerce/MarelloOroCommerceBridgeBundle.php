<?php

namespace Marello\Bridge\MarelloOroCommerce;

use Marello\Bridge\MarelloOroCommerce\DependencyInjection\CompilerPass\DuplicatedServicesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloOroCommerceBridgeBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DuplicatedServicesCompilerPass());
    }
}
