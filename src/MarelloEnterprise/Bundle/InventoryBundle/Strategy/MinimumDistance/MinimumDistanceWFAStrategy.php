<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumDistance;

use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\Order;
use MarelloEnterprise\Bundle\AddressBundle\Distance\AddressesDistanceCalculatorInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategyInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;

class MinimumDistanceWFAStrategy implements WFAStrategyInterface, FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;

    /**
     * @var AddressesDistanceCalculatorInterface
     */
    private $distanceCalculator;

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'min_distance';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marelloenterprise.inventory.strategies.min_distance';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->featureChecker->isFeatureEnabled('address_geocoding');
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
    public function getWarehouses(Order $order, array $initialResults = [])
    {
        if (!$this->isEnabled() || empty($initialResults)) {
            return $initialResults;
        }

        $destinationAddress = $order->getShippingAddress();
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
