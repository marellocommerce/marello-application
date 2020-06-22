<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use MarelloEnterprise\Bundle\InventoryBundle\Form\EventListener\InventoryLevelSubscriber;

class InventoryLevelFormSubscriberCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('marello_inventory.form.event_listener.inventory_level_subscriber');
        $definition->setClass(InventoryLevelSubscriber::class);
    }
}
