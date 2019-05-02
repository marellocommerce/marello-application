<?php

namespace Marello\Bundle\InventoryBundle\Model\InventoryBalancer;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;

class BalancedResultObject
{
    /** @var SalesChannelGroup $group */
    private $group;

    /** @var int $inventoryQty */
    private $inventoryQty;

    public function __construct(SalesChannelGroup $group, $inventoryQty)
    {
        $this->group = $group;
        $this->inventoryQty = $inventoryQty;
    }

    /**
     * Get assigned SalesChannelGroup
     * @return SalesChannelGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Get inventory qty
     * @return int
     */
    public function getInventoryQty()
    {
        return $this->inventoryQty;
    }
}
