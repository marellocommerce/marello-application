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
     * @return BalancedResultObject[]
     */
    public function getResults(
        ProductInterface $product,
        ArrayAccess $salesChannelGroups,
        $inventoryTotal
    ) {
        $result = [];
        $totalChannelGroups = count($salesChannelGroups);
        $totalPerChannelRaw = ($inventoryTotal / $totalChannelGroups);
        $totalPerChannelPrecision = round($totalPerChannelRaw, 0, PHP_ROUND_HALF_DOWN);
        $calculatedResult = ($totalPerChannelPrecision * $totalChannelGroups);

        foreach ($salesChannelGroups as $scg) {
            if ((float)$calculatedResult !== (float)$inventoryTotal) {
                $leftOverTotal = ($inventoryTotal - $calculatedResult);
                if ($leftOverTotal === (float) 0) {
                    $result[$scg->getId()] = $this->createResultObject($scg, $totalPerChannelPrecision);
                }
            }
            $result[$scg->getId()] = $this->createResultObject($scg, $calculatedResult);
        }

        return $result;
    }
}
