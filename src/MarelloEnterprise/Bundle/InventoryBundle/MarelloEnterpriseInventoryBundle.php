<?php

namespace MarelloEnterprise\Bundle\InventoryBundle;

use MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass\ImexportInventoryLevelStrategyCompilerPass;
use MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass\InventoryLevelFormSubscriberCompilerPass;
use MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass\InventoryLevelsGridCompilerPass;
use MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass\InventoryManagerCompilerPass;
use MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass\WFAStrategiesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloEnterpriseInventoryBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new InventoryManagerCompilerPass());
        $container->addCompilerPass(new InventoryLevelFormSubscriberCompilerPass());
        $container->addCompilerPass(new ImexportInventoryLevelStrategyCompilerPass());
        $container->addCompilerPass(new WFAStrategiesCompilerPass());
        $container->addCompilerPass(new InventoryLevelsGridCompilerPass());
        parent::build($container);
    }
}
