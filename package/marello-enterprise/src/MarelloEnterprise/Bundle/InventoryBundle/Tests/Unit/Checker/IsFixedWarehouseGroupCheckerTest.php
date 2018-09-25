<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Checker;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Checker\IsFixedWarehouseGroupChecker;
use Oro\Component\Testing\Unit\EntityTrait;

class IsFixedWarehouseGroupCheckerTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var IsFixedWarehouseGroupChecker
     */
    protected $isFixedWarehouseGroupChecker;

    protected function setUp()
    {
        $this->isFixedWarehouseGroupChecker = new IsFixedWarehouseGroupChecker();
    }

    /**
     * @dataProvider checkDataProvider
     * @param array $warehouseTypes
     * @param bool $result
     */
    public function testCheck(array $warehouseTypes, $result)
    {
        $warehouses = [];
        foreach ($warehouseTypes as $warehouseType) {
            $warehouses[] = $this->buildWarehouse($warehouseType);
        }
        /** @var WarehouseGroup $group */
        $group = $this->getEntity(WarehouseGroup::class, ['warehouses' => $warehouses]);

        static::assertEquals($result, $this->isFixedWarehouseGroupChecker->check($group));
    }

    /**
     * @return array
     */
    public function checkDataProvider()
    {
        return [
            [
                'warehouseTypes' => [
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED,
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL,
                ],
                'result' => false
            ],
            [
                'warehouseTypes' => [],
                'result' => false
            ],
            [
                'warehouseTypes' => [
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED,
                ],
                'result' => true
            ]
        ];
    }

    /**
     * @param string $typeName
     * @return Warehouse
     */
    private function buildWarehouse($typeName)
    {
        $whType = $this->getEntity(WarehouseType::class, ['name' => $typeName]);
        return $this->getEntity(Warehouse::class, ['warehouseType' => $whType]);
    }
}
