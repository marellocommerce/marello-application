<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\IntegrationBundle\Exception\InvalidConfigurationException;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class InventoryBalancerManager
{
    const SELECTED_BALANCER_CONFIG = 'marello_inventory.selected_balancer';

    /** @var InventoryBalancerRegistry $registry */
    protected $registry;

    /** @var ConfigManager $configManager */
    protected $configManager;

    /**
     * InventoryBalancerManager constructor.
     * @param InventoryBalancerRegistry $registry
     * @param ConfigManager $configManager
     */
    public function __construct(
        InventoryBalancerRegistry $registry,
        ConfigManager $configManager
    ) {
        $this->registry = $registry;
        $this->configManager = $configManager;
    }

    public function balanceInventory(InventoryUpdateContext $context)
    {
        $inventoryBalancer = $this->getSelectedInventoryBalancer();

        if (!$inventoryBalancer instanceof InventoryBalancerInterface) {
            throw new InvalidConfigurationException(sprintf('Inventory Balancer must implement %s', InventoryBalancerInterface::class));
        }

        $inventoryBalancer->process($context);
    }

    protected function getSelectedInventoryBalancer()
    {
        $selectedBalancerAlias = $this->configManager->get(self::SELECTED_BALANCER_CONFIG);
        return $this->registry->getInventoryBalancer($selectedBalancerAlias);
    }
}
