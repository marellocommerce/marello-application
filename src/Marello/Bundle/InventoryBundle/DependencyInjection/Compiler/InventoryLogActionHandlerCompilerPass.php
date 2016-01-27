<?php

namespace Marello\Bundle\InventoryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class InventoryLogActionHandlerCompilerPass implements CompilerPassInterface
{
    const TAG_NAME       = 'marello_inventory.log.action_handler';
    const LOGGER_SERVICE = 'marello_inventory.logging.inventory_logger';

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::LOGGER_SERVICE)) {
            return;
        }

        $loggerDefinition = $container->findDefinition(self::LOGGER_SERVICE);
        $taggedServiceIds = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($taggedServiceIds as $serviceId => $tags) {
            $loggerDefinition->addMethodCall('addActionHandler', [$tags['type'], new Reference($serviceId)]);
        }
    }
}
