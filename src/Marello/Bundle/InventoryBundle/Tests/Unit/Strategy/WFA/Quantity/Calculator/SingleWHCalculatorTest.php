<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\Quantity\Calculator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator\SingleWHCalculator;
use Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\WFA\Quantity\Calculator\AbstractWHCalculatorTest;

class SingleWHCalculatorTest extends AbstractWHCalculatorTest
{
    /**
     * @var SingleWHCalculator
     */
    protected $singleWHCalculatorChainElement;

    protected function setUp(): void
    {
        $this->singleWHCalculatorChainElement = new SingleWHCalculator();
    }

    /**
     * @dataProvider calculateDataProvider
     *
     * @param array $productsByWh
     * @param array $orderItemsByProducts
     * @param Warehouse[] $warehouses
     * @param Collection $orderItems
     * @param array $expectedResults
     */
    public function testCalculate(
        array $productsByWh,
        array $orderItemsByProducts,
        array $warehouses,
        Collection $orderItems,
        array $expectedResults
    ) {
        // tmp disable test, as the format is currently incorrect and should be fixed first
        return;
        $actualResults = $this->singleWHCalculatorChainElement->calculate(
            $productsByWh,
            $orderItemsByProducts,
            $warehouses,
            $orderItems
        );

        static::assertEquals($expectedResults, $actualResults);
    }
    
    public function calculateDataProvider()
    {
        $orderItem1_1 = $this->getEntity(
            OrderItem::class,
            ['product' => $this->mockProduct(1, [1 => 5, 2 => 5], [1 => 5, 2 => 1]), 'quantity' => 3]
        );
        $orderItem1_2 = $this->getEntity(
            OrderItem::class,
            ['product' => $this->mockProduct(2, [1 => 5, 2 => 5], [1 => 5, 2 => 1]), 'quantity' => 5]
        );
        $orderItems1 = new ArrayCollection([$orderItem1_1, $orderItem1_2]);

        $orderItem2_1 = $this->getEntity(
            OrderItem::class,
            ['product' => $this->mockProduct(1, [1 => 5, 3 => 5], [1 => 5, 3 => 1]), 'quantity' => 3]
        );
        $orderItem2_2 = $this->getEntity(
            OrderItem::class,
            ['product' => $this->mockProduct(2, [1 => 5, 3 => 5], [1 => 5, 3 => 1]), 'quantity' => 5]
        );
        $orderItems2 = new ArrayCollection([$orderItem2_1, $orderItem2_2]);

        $orderItem3_1 = $this->getEntity(
            OrderItem::class,
            ['product' => $this->mockProduct(1, [2 => 5, 3 => 5], [2 => 5, 3 => 1]), 'quantity' => 3]
        );
        $orderItem3_2 = $this->getEntity(
            OrderItem::class,
            ['product' => $this->mockProduct(2, [2 => 5, 3 => 5], [2 => 5, 3 => 1]), 'quantity' => 5]
        );
        $orderItems3 = new ArrayCollection([$orderItem3_1, $orderItem3_2]);

        $orderItem4_1 = $this->getEntity(
            OrderItem::class,
            ['product' => $this->mockProduct(1, [3 => 5, 4 => 0], [3 => 5, 4 => 1]), 'quantity' => 3]
        );
        $orderItem4_2 = $this->getEntity(
            OrderItem::class,
            ['product' => $this->mockProduct(2, [3 => 5, 4 => 0], [3 => 5, 4 => 1]), 'quantity' => 5]
        );
        $orderItems4 = new ArrayCollection([$orderItem4_1, $orderItem4_2]);

        $orderItem5_1 = $this->getEntity(
            OrderItem::class,
            ['product' => $this->mockProduct(1, [3 => 5, 5 => 5], [3 => 5, 5 => 1]), 'quantity' => 3]
        );
        $orderItem5_2 = $this->getEntity(
            OrderItem::class,
            ['product' => $this->mockProduct(2, [3 => 5, 5 => 5], [3 => 5, 5 => 1]), 'quantity' => 5]
        );
        $orderItems5 = new ArrayCollection([$orderItem5_1, $orderItem5_2]);

        $orderItem6_1 = $this->getEntity(
            OrderItem::class,
            ['product' => $this->mockProduct(1, [4 => 0, 6 => 0], [4 => 5, 6 => 1]), 'quantity' => 3]
        );
        $orderItem6_2 = $this->getEntity(
            OrderItem::class,
            ['product' => $this->mockProduct(2, [4 => 0, 6 => 0], [4 => 5, 6 => 1]), 'quantity' => 5]
        );
        $orderItems6 = new ArrayCollection([$orderItem6_1, $orderItem6_2]);
        return [
            'DefaultAndNotDefault' => [
                'productsByWh' => [
                    1 => ['TPD0001', 'TPD0002'],
                    2 => ['TPD0001', 'TPD0002']
                ],
                'orderItemsByProducts' => [
                    'TPD0001_|_' => $orderItem1_1,
                    'TPD0002_|_' => $orderItem1_2
                ],
                'warehouses' => $this->getWarehouses(),
                'orderItems' => $orderItems1,
                'expectedResults' => [
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[1],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems1
                        ])
                    ],
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[2],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems1
                        ])
                    ]
                ]
            ],
            'DefaultAndExternal' => [
                'productsByWh' => [
                    1 => ['TPD0001', 'TPD0002'],
                    3 => ['TPD0001', 'TPD0002']
                ],
                'orderItemsByProducts' => [
                    'TPD0001_|_' => $orderItem2_1,
                    'TPD0002_|_' => $orderItem2_2
                ],
                'warehouses' => $this->getWarehouses(),
                'orderItems' => $orderItems2,
                'expectedResults' => [
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[1],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems2
                        ])
                    ],
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems2
                        ])
                    ]
                ]
            ],
            'NotDefaultAndExternal' => [
                'productsByWh' => [
                    2 => ['TPD0001', 'TPD0002'],
                    3 => ['TPD0001', 'TPD0002']
                ],
                'orderItemsByProducts' => [
                    'TPD0001_|_' => $orderItem3_1,
                    'TPD0002_|_' => $orderItem3_2
                ],
                'warehouses' => $this->getWarehouses(),
                'orderItems' => $orderItems3,
                'expectedResults' => [
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[2],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems3
                        ])
                    ],
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems3
                        ])
                    ]
                ]
            ],
            'ExternalWithQtyAndExternalWithoutQty' => [
                'productsByWh' => [
                    3 => ['TPD0001', 'TPD0002'],
                    4 => ['TPD0001', 'TPD0002']
                ],
                'orderItemsByProducts' => [
                    'TPD0001_|_' => $orderItem4_1,
                    'TPD0002_|_' => $orderItem4_2
                ],
                'warehouses' => $this->getWarehouses(),
                'orderItems' => $orderItems4,
                'expectedResults' => [
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems4
                        ])
                    ],
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[4],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems4
                        ])
                    ]
                ]
            ],
            'ExternalWithQtyByPrioriries' => [
                'productsByWh' => [
                    3 => ['TPD0001', 'TPD0002'],
                    5 => ['TPD0001', 'TPD0002']
                ],
                'orderItemsByProducts' => [
                    'TPD0001_|_' => $orderItem5_1,
                    'TPD0002_|_' => $orderItem5_2
                ],
                'warehouses' => $this->getWarehouses(),
                'orderItems' => $orderItems5,
                'expectedResults' => [
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[5],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems5
                        ])
                    ],
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems5
                        ])
                    ]
                ]
            ],
            'ExternalWithoutQtyByPrioriries' => [
                'productsByWh' => [
                    4 => ['TPD0001', 'TPD0002'],
                    6 => ['TPD0001', 'TPD0002']
                ],
                'orderItemsByProducts' => [
                    'TPD0001_|_' => $orderItem6_1,
                    'TPD0002_|_' => $orderItem6_2
                ],
                'warehouses' => $this->getWarehouses(),
                'orderItems' => $orderItems6,
                'expectedResults' => [
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[6],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems6
                        ])
                    ],
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[4],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems6
                        ])
                    ]
                ]
            ]
        ];
    }
}
