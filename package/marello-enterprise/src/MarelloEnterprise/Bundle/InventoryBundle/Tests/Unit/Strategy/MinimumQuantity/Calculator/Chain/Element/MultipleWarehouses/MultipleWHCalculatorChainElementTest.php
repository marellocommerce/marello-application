<?php

// @codingStandardsIgnoreStart
namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumQuantity\Calculator\Chain\Element\MultipleWarehouses;

// @codingStandardsIgnoreEnd

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\MultipleWarehouses\
MultipleWHCalculatorChainElement;
use Oro\Component\Testing\Unit\EntityTrait;

class MultipleWHCalculatorChainElementTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var MultipleWHCalculatorChainElement
     */
    protected $multipleWHCalculatorChainElement;

    protected function setUp()
    {
        $this->multipleWHCalculatorChainElement = new MultipleWHCalculatorChainElement();
    }

    public function testCalculate()
    {
        $product1 = $this->getEntity(Product::class, ['sku' => 'TPD0001']);
        $product2 = $this->getEntity(Product::class, ['sku' => 'TPD0002']);
        $product3 = $this->getEntity(Product::class, ['sku' => 'TPD0003']);

        $warehouse1 = $this->getEntity(Warehouse::class, ['id' => 1, 'default' => true]);
        $warehouse2 = $this->getEntity(Warehouse::class, ['id' => 2]);
        $warehouse3 = $this->getEntity(Warehouse::class, ['id' => 3]);

        $orderItem1 = $this->getEntity(OrderItem::class, ['product' => $product1, 'quantity' => 1]);
        $orderItem2 = $this->getEntity(OrderItem::class, ['product' => $product2, 'quantity' => 1]);
        $orderItem3 = $this->getEntity(OrderItem::class, ['product' => $product3, 'quantity' => 1]);

        $productsByWh = [
            1 => ['TPD0001', 'TPD0003'],
            2 => ['TPD0001', 'TPD0002'],
            3 => ['TPD0002', 'TPD0003']
        ];
        $orderItemsByProducts = [
            'TPD0001' => $orderItem1,
            'TPD0002' => $orderItem2,
            'TPD0003' => $orderItem3
        ];
        $warehouses = [
            1 => $warehouse1,
            2 => $warehouse2,
            3 => $warehouse3,
        ];
        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $orderItems **/
        $orderItems = new ArrayCollection([$orderItem1, $orderItem2, $orderItem3]);

        $expectedResult = [
            [
                'TPD0001|TPD0002' => new OrderWarehouseResult([
                    OrderWarehouseResult::WAREHOUSE_FIELD => $warehouse2,
                    OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem1, $orderItem2])
                ]),
                'TPD0003' => new OrderWarehouseResult([
                    OrderWarehouseResult::WAREHOUSE_FIELD => $warehouse1,
                    OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem3])
                ])
            ],
            [
                'TPD0002|TPD0003' => new OrderWarehouseResult([
                    OrderWarehouseResult::WAREHOUSE_FIELD => $warehouse3,
                    OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem2, $orderItem3])
                ]),
                'TPD0001' => new OrderWarehouseResult([
                    OrderWarehouseResult::WAREHOUSE_FIELD => $warehouse1,
                    OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem1])
                ])
            ],
            [
                'TPD0001|TPD0003' => new OrderWarehouseResult([
                    OrderWarehouseResult::WAREHOUSE_FIELD => $warehouse1,
                    OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem1, $orderItem3])
                ]),
                'TPD0002' => new OrderWarehouseResult([
                    OrderWarehouseResult::WAREHOUSE_FIELD => $warehouse2,
                    OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem2])
                ])
            ],
        ];
        $actualResult = $this->multipleWHCalculatorChainElement->calculate(
            $productsByWh,
            $orderItemsByProducts,
            $warehouses,
            $orderItems
        );

        static::assertEquals($expectedResult, $actualResult);
    }
}
