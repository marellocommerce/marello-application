<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element;

use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\MinQtyWHCalculatorInterface;

abstract class AbstractWHCalculatorChainElement implements MinQtyWHCalculatorInterface
{
    /**
     * @var MinQtyWHCalculatorInterface|null
     */
    private $successor;

    /**
     * @param MinQtyWHCalculatorInterface $whCalculator
     */
    public function setSuccessor(MinQtyWHCalculatorInterface $whCalculator)
    {
        $this->successor = $whCalculator;
    }

    /**
     * @return MinQtyWHCalculatorInterface|null
     */
    protected function getSuccessor()
    {
        return $this->successor;
    }
    
    /**
     * @param OrderWarehouseResult[] $results
     * @return boolean
     */
    protected function hasDefaultWarehouse(array $results)
    {
        foreach ($results as $result) {
            if ($result->getWarehouse()->isDefault()) {
                return true;
            }
        }
        return false;
    }
}
