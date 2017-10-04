<?php

namespace MarelloEnterprise\Bundle\InventoryBundle;

use MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass\
ImexportInventoryLevelStrategyCompilerPass;
use MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass\InventoryLevelFormSubscriberCompilerPass;
use MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass\InventoryManagerCompilerPass;
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
        parent::build($container);
    }
}
