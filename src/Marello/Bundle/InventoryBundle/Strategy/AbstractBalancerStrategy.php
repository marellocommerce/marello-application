<?php

namespace Marello\Bundle\InventoryBundle\Strategy;

use ArrayAccess;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\BalancedResultObject;

/**
 * Class EqualDivisionBalancerStrategy
 * @package MarelloEnterprise\Bundle\InventoryBundle\Strategy\EqualDivision
 * This balancer will balance the total inventory of a product into equal amount
 * for the total sales channel count.
 */
abstract class AbstractBalancerStrategy implements BalancerStrategyInterface
{
    /**
     * Function should be overridden by the derived BalancerStrategy
     * {@inheritdoc}
     */
    public function getResults(
        ProductInterface $product,
        ArrayAccess $salesChannelGroups,
        $inventoryTotal
    ) {
        //Function should be overridden by the derived BalancerStrategy
    }

    /**
     * Create Result object for balanced inventory
     * @param SalesChannelGroup $group
     * @param $inventoryQty
     * @return BalancedResultObject
     */
    public function createResultObject(SalesChannelGroup $group, $inventoryQty)
    {
        return new BalancedResultObject($group, $inventoryQty);
    }
}
