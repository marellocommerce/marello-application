<?php

namespace MarelloEnterprise\Bundle\InventoryBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass\InventoryManagerCompilerPass;
use MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass\InventoryLevelFormSubscriberCompilerPass;

class MarelloEnterpriseInventoryBundle extends Bundle
{
    public function getParent()
    {
        return 'MarelloInventoryBundle';
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new InventoryManagerCompilerPass());
        $container->addCompilerPass(new InventoryLevelFormSubscriberCompilerPass());
        parent::build($container);
    }
}
