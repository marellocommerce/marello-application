<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Oro\Bundle\IntegrationBundle\Exception\InvalidConfigurationException;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Entity\StockLevel;

class InventoryManager
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

    public function updateInventoryItems($items, InventoryUpdateContext $context)
    {
//        [
//            [
//                'item' => $invItem,
//                'qty' => 10
//            ],
//            [
//                'item' => $inveIem,
//                'qty' => $irem
//            ]
//        ]
        foreach ($items as $k=> $item) {

        }
    }

    protected function getSelectedInventoryBalancer()
    {
        $selectedBalancerAlias = $this->configManager->get(self::SELECTED_BALANCER_CONFIG);
        return $this->registry->getInventoryBalancer($selectedBalancerAlias);
    }
}
