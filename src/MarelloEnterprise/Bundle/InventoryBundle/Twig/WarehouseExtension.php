<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Twig;

use MarelloEnterprise\Bundle\InventoryBundle\Checker\IsFixedWarehouseGroupChecker;
use MarelloEnterprise\Bundle\InventoryBundle\Manager\InventoryManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WarehouseExtension extends AbstractExtension
{
    const NAME = 'marelloenterprise_warehouse';

    public function __construct(
        protected IsFixedWarehouseGroupChecker $isFixedWarehouseGroupChecker,
        protected InventoryManager $inventoryManager
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'marello_inventory_is_fixed_warehousegroup',
                [$this->isFixedWarehouseGroupChecker, 'check']
            ),
            new TwigFunction(
                'get_expected_inventory_total',
                [$this, 'getExpectedInventoryTotal']
            ),
        ];
    }

    public function getExpectedInventoryTotal($entity)
    {
        return $this->inventoryManager->getExpectedInventoryTotal($entity);
    }
}
