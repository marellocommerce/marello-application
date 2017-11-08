<?php

namespace Marello\Bundle\InventoryBundle\Strategy\EqualDivision;

use ArrayAccess;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\BalancedResultObject;
use Marello\Bundle\InventoryBundle\Strategy\AbstractBalancerStrategy;

/**
 * Class EqualDivisionBalancerStrategy
 * @package MarelloEnterprise\Bundle\InventoryBundle\Strategy\EqualDivision
 * This balancer will balance the total inventory of a product into equal amount
 * for the total sales channel count.
 */
class EqualDivisionBalancerStrategy extends AbstractBalancerStrategy
{
    const IDENTIFIER = 'equal_division';
    const LABEL = 'marello.inventory.balancing.strategies.equal_division';

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getLabel()
    {
        return self::LABEL;
    }

    /**
     * {@inheritdoc}
     * @return BalancedResultObject[]
     */
    public function getResults(
        ProductInterface $product,
        ArrayAccess $salesChannelGroups,
        $inventoryTotal
    ) {
        $result = [];
        $totalChannelGroups = count($salesChannelGroups);
        $totalChannelGroupInventory = $this->calculateTotalPerChannelGroup($totalChannelGroups, $inventoryTotal);
        $remainder = $this->calculateRemainder($totalChannelGroupInventory, $totalChannelGroups, $inventoryTotal);

        foreach ($salesChannelGroups as $scg) {
            $inventory = $totalChannelGroupInventory;
            if ($remainder > 0) {
                // increase the total for this channel in order to distribute the left over of the totals
                $inventory++;
            }

            $result[$scg->getId()] = $this->createResultObject($scg, $inventory);
            // decrease left over total so we get to 0 and don't add more inventory than there actually is
            $remainder--;
        }

        return $result;
    }

    /**
     * Calculate the remainder of the total inventory
     * @param $totalChannelGroupInventory
     * @param $totalChannelGroups
     * @param $inventoryTotal
     * @return mixed
     */
    private function calculateRemainder($totalChannelGroupInventory, $totalChannelGroups, $inventoryTotal)
    {
        $calculatedResult = ($totalChannelGroupInventory * $totalChannelGroups);
        return ($inventoryTotal - $calculatedResult);
    }

    /**
     * Calculate total per sales channel
     * @param $totalChannelGroups
     * @param $inventoryTotal
     * @return float
     */
    private function calculateTotalPerChannelGroup($totalChannelGroups, $inventoryTotal)
    {
        $totalPerChannelRaw = ($inventoryTotal / $totalChannelGroups);
        return floor($totalPerChannelRaw);
    }
}
