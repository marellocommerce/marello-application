<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumQuantity;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\MinQtyWHCalculatorInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\MinimumQuantityWFAStrategy;
use Oro\Component\Testing\Unit\EntityTrait;

class MinimumQuantityWFAStrategyTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var MinQtyWHCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $minQtyWHCalculator;

    /**
     * @var MinimumQuantityWFAStrategy
     */
    protected $minimumQuantityWFAStrategy;

    protected function setUp()
    {
        $this->minQtyWHCalculator = $this->createMock(MinQtyWHCalculatorInterface::class);
        $this->minimumQuantityWFAStrategy = new MinimumQuantityWFAStrategy($this->minQtyWHCalculator);
    }

    public function testGetIdentifier()
    {
        static::assertEquals(
            MinimumQuantityWFAStrategy::IDENTIFIER,
            $this->minimumQuantityWFAStrategy->getIdentifier()
        );
    }

    public function testGetLabel()
    {
        static::assertEquals(
            MinimumQuantityWFAStrategy::LABEL,
            $this->minimumQuantityWFAStrategy->getLabel()
        );
    }

    public function testIsEnabled()
    {
        static::assertEquals(
            true,
            $this->minimumQuantityWFAStrategy->isEnabled()
        );
    }

    public function testGetWarehouseResults()
    {
        $product1 = $this->getEntity(Product::class, ['sku' => 'TPD0001']);
        $product2 = $this->getEntity(Product::class, ['sku' => 'TPD0002']);
        $product3 = $this->getEntity(Product::class, ['sku' => 'TPD0003']);

        $warehouse1 = $this->getEntity(Warehouse::class, ['id' => 1, 'default' => true]);
        $warehouse2 = $this->getEntity(Warehouse::class, ['id' => 2]);
        $warehouse3 = $this->getEntity(Warehouse::class, ['id' => 3]);

        $inventoryLevel1 = $this->getEntity(InventoryLevel::class, ['inventory' => 10, 'warehouse' => $warehouse1]);
        $inventoryLevel2 = $this->getEntity(InventoryLevel::class, ['inventory' => 10, 'warehouse' => $warehouse2]);
        $inventoryLevel3 = $this->getEntity(InventoryLevel::class, ['inventory' => 10, 'warehouse' => $warehouse3]);

        $inventoryItem1 = $this->getEntity(
            InventoryItem::class,
            [
                'inventoryLevels' => [$inventoryLevel1, $inventoryLevel2]
            ],
            [
                $warehouse1,
                $product1
            ]
        );
        $inventoryItem2 = $this->getEntity(
            InventoryItem::class,
            [
                'inventoryLevels' => [$inventoryLevel2, $inventoryLevel3]
            ],
            [
                $warehouse2,
                $product2
            ]
        );
        $inventoryItem3 = $this->getEntity(
            InventoryItem::class,
            [
                'inventoryLevels' => [$inventoryLevel1, $inventoryLevel3]
            ],
            [
                $warehouse3,
                $product3
            ]
        );

        $product1->addInventoryItem($inventoryItem1);
        $product2->addInventoryItem($inventoryItem2);
        $product3->addInventoryItem($inventoryItem3);

        $orderItem1 = $this->getEntity(OrderItem::class, ['product' => $product1, 'quantity' => 1]);
        $orderItem2 = $this->getEntity(OrderItem::class, ['product' => $product2, 'quantity' => 1]);
        $orderItem3 = $this->getEntity(OrderItem::class, ['product' => $product3, 'quantity' => 1]);

        /** @var Order|\PHPUnit_Framework_MockObject_MockObject $order **/
        $order = $this->getEntity(Order::class, ['items' => [$orderItem1, $orderItem2, $orderItem3]]);
        $initialResults = [];

        $productsByWh = [
            1 => ['TPD0001', 'TPD0003'],
            2 => ['TPD0001', 'TPD0002'],
            3 => ['TPD0002', 'TPD0003']
        ];
        $orderItemsByProducts = [
            'TPD0001' => $orderItem1,
            'TPD0002' => $orderItem2,
            'TPD0003' => $orderItem3,
        ];
        $warehouses = [
            1 => $warehouse1,
            2 => $warehouse2,
            3 => $warehouse3,
        ];

        $this->minQtyWHCalculator
            ->expects(static::once())
            ->method('calculate')
            ->with($productsByWh, $orderItemsByProducts, $warehouses, $order->getItems());

        $this->minimumQuantityWFAStrategy->getWarehouseResults($order, $initialResults);
    }
}
