<?php

namespace Marello\Bundle\InventoryBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Marello\Bundle\InventoryBundle\DependencyInjection\CompilerPass\BalancerStrategiesCompilerPass;

class MarelloInventoryBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new BalancerStrategiesCompilerPass());

        parent::build($container);
    }
}
