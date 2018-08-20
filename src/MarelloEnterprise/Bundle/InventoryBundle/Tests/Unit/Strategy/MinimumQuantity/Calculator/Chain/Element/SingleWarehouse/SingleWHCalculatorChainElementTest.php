<?php

// @codingStandardsIgnoreStart
namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumQuantity\Calculator\Chain\Element\SingleWarehouse;

// @codingStandardsIgnoreEnd

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\SingleWarehouse\
SingleWHCalculatorChainElement;
use Oro\Component\Testing\Unit\EntityTrait;

class SingleWHCalculatorChainElementTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var SingleWHCalculatorChainElement
     */
    protected $singleWHCalculatorChainElement;

    protected function setUp()
    {
        $this->singleWHCalculatorChainElement = new SingleWHCalculatorChainElement();
    }

    public function testCalculate()
    {
        $product1 = $this->getEntity(Product::class, ['sku' => 'TPD0001']);
        $product2 = $this->getEntity(Product::class, ['sku' => 'TPD0002']);

        $warehouse1 = $this->getEntity(Warehouse::class, ['id' => 1, 'default' => true]);
        $warehouse2 = $this->getEntity(Warehouse::class, ['id' => 2]);
        $warehouse3 = $this->getEntity(Warehouse::class, ['id' => 3]);

        $orderItem1 = $this->getEntity(OrderItem::class, ['product' => $product1, 'quantity' => 1]);
        $orderItem2 = $this->getEntity(OrderItem::class, ['product' => $product2, 'quantity' => 1]);

        $productsByWh = [
            1 => ['TPD0001', 'TPD0002'],
            2 => ['TPD0001', 'TPD0002']
        ];
        $orderItemsByProducts = [
            'TPD0001' => $orderItem1,
            'TPD0002' => $orderItem2
        ];
        $warehouses = [
            1 => $warehouse1,
            2 => $warehouse2,
            3 => $warehouse3,
        ];
        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $orderItems **/
        $orderItems = new ArrayCollection([$orderItem1, $orderItem2]);

        $expectedResult = [
            [
                'TPD0001|TPD0002' => new OrderWarehouseResult([
                    OrderWarehouseResult::WAREHOUSE_FIELD => $warehouse1,
                    OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems
                ])
            ],
            [
                'TPD0001|TPD0002' => new OrderWarehouseResult([
                    OrderWarehouseResult::WAREHOUSE_FIELD => $warehouse2,
                    OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems
                ])
            ]
        ];
        $actualResult = $this->singleWHCalculatorChainElement->calculate(
            $productsByWh,
            $orderItemsByProducts,
            $warehouses,
            $orderItems
        );

        static::assertEquals($expectedResult, $actualResult);
    }
}
