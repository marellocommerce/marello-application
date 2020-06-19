<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle;

use MarelloEnterprise\Bundle\ReplenishmentBundle\DependencyInjection\CompilerPass\ReplenishmentStrategiesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloEnterpriseReplenishmentBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ReplenishmentStrategiesCompilerPass());

        parent::build($container);
    }
}
