<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /** @var InventoryManagerInterface $inventoryManager */
    protected $inventoryManager;

    /**
     * InventoryBalancerManager constructor.
     * @param InventoryBalancerRegistry $registry
     * @param ConfigManager             $configManager
     * @param EventDispatcherInterface  $dispatcher
     * @param InventoryManagerInterface $inventoryManager
     */
    public function __construct(
        InventoryBalancerRegistry   $registry,
        ConfigManager               $configManager,
        EventDispatcherInterface    $dispatcher,
        InventoryManagerInterface   $inventoryManager
    ) {
        $this->registry             = $registry;
        $this->configManager        = $configManager;
        $this->eventDispatcher      = $dispatcher;
        $this->inventoryManager     = $inventoryManager;
    }

    /**
     * @param InventoryUpdateContext $context
     */
    public function balanceInventory(InventoryUpdateContext $context)
    {
        $inventoryBalancer = $this->getSelectedInventoryBalancer();

        if (!$inventoryBalancer instanceof InventoryBalancerInterface) {
            throw new InvalidConfigurationException(sprintf('Inventory Balancer must implement %s', InventoryBalancerInterface::class));
        }

        $inventoryBalancer->setInventoryUpdateContext($context);
        $inventoryBalancer->setDispatcher($this->eventDispatcher);
        $inventoryBalancer->setInventoryManager($this->inventoryManager);
        $inventoryBalancer->process();
    }

    /**
     * @return InventoryBalancerInterface
     */
    protected function getSelectedInventoryBalancer()
    {
        $selectedBalancerAlias = $this->configManager->get(self::SELECTED_BALANCER_CONFIG);
        return $this->registry->getInventoryBalancer($selectedBalancerAlias);
    }
}
