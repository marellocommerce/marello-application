<?php

namespace Marello\Bundle\InventoryBundle\Manager\Balancer;

use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\InventoryBundle\Manager\InventoryManagerInterface;
use Marello\Bundle\InventoryBundle\Manager\InventoryBalancerInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

abstract class AbstractInventoryBalancer implements InventoryBalancerInterface
{
    /** @var InventoryManager $inventoryManager */
    protected $inventoryManager;

    /** @var InventoryUpdateContext $context */
    protected $context;

    /**
     * @param InventoryManagerInterface $inventoryManager
     */
    public function __construct(InventoryManagerInterface $inventoryManager)
    {
        $this->inventoryManager = $inventoryManager;
    }

    /**
     * Set context
     * @param InventoryUpdateContext $context
     */
    public function setInventoryUpdateContext(InventoryUpdateContext $context)
    {
        $this->context = $context;
    }

    /**
     * Process balancing inventory and send items for update
     * @throws \Exception
     */
    public function process()
    {
        if (!$this->context) {
            throw new \Exception('Cannot process without a context being set, please call setInventoryUpdateContext before calling process');
        }

        if ($this->canBalance()) {
            $this->balanceInventory($this->context);

            if ($this->canUpdateInventory()) {
                $this->getInventoryManager()->updateInventoryItems($this->context);
            }
        }
    }

    /**
     * Check if we can balance the inventory, by checking if there is a product
     * @return bool
     */
    protected function canBalance()
    {
        return ($this->context->getProduct()) ? true : false;
    }

    /**
     * Check if we can update the inventory, by checking if there are items to update
     * @return bool
     */
    protected function canUpdateInventory()
    {
        // put logger here so we can log that there are in fact no items to update...
        var_dump('hi');
        return (count($this->context->getItems()) > 0) ? true : false;
    }

    /**
     * @return InventoryManagerInterface
     */
    protected function getInventoryManager()
    {
        return $this->inventoryManager;
    }

    /**
     * @param InventoryUpdateContext $context
     */
    abstract protected function balanceInventory($context);
}
