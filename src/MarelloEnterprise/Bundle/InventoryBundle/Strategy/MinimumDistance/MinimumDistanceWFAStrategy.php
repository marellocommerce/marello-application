<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumDistance;

use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\Order;
use MarelloEnterprise\Bundle\AddressBundle\Distance\AddressesDistanceCalculatorInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategyInterface;

class MinimumDistanceWFAStrategy implements WFAStrategyInterface
{
    const IDENTIFIER = 'min_distance';
    const LABEL = 'marelloenterprise.inventory.strategies.min_distance';

    /**
     * @var AddressesDistanceCalculatorInterface
     */
    private $distanceCalculator;

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return self::LABEL;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @param AddressesDistanceCalculatorInterface $distanceCalculator
     */
    public function __construct(AddressesDistanceCalculatorInterface $distanceCalculator)
    {
        $this->distanceCalculator = $distanceCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function getWarehouseResults(Order $order, array $initialResults = [])
    {
        if (!$this->isEnabled() || empty($initialResults)) {
            return $initialResults;
        }

        $destinationAddress = $order->getShippingAddress();
        if (!$destinationAddress) {
            return $initialResults;
        }
        $distances = [];
        foreach ($initialResults as $key => $warehousesSet) {
            $distance = 0;
            /** @var OrderWarehouseResult $warehouseResult */
            foreach ($warehousesSet as $warehouseResult) {
                $originAddress = $warehouseResult->getWarehouse()->getAddress();
                $distance += $this->distanceCalculator->calculate($originAddress, $destinationAddress);
            }
            $distances[$key] = $distance;
        }

        return [$initialResults[array_search(min($distances), $distances)]];
    }
}
