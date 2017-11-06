<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Twig;

use MarelloEnterprise\Bundle\InventoryBundle\Checker\IsFixedWarehouseGroupChecker;

class WarehouseExtension extends \Twig_Extension
{
    const NAME = 'marelloenterprise_warehouse';

    /**
     * @var IsFixedWarehouseGroupChecker
     */
    protected $isFixedWarehouseGroupChecker;

    /**
     * @param IsFixedWarehouseGroupChecker $isFixedWarehouseGroupChecker
     */
    public function __construct(IsFixedWarehouseGroupChecker $isFixedWarehouseGroupChecker)
    {
        $this->isFixedWarehouseGroupChecker = $isFixedWarehouseGroupChecker;
    }

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
            new \Twig_SimpleFunction(
                'marello_inventory_is_fixed_warehousegroup',
                [$this->isFixedWarehouseGroupChecker, 'check']
            )
        ];
    }
}
