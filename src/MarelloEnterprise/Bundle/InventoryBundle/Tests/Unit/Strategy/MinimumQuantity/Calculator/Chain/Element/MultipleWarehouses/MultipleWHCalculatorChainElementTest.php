<?php

// @codingStandardsIgnoreStart
namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumQuantity\Calculator\Chain\Element\MultipleWarehouses;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\MultipleWarehouses\MultipleWHCalculatorChainElement;
use MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumQuantity\Calculator\Chain\Element\AbstractWHCalculatorChainElementTest;

// @codingStandardsIgnoreEnd

class MultipleWHCalculatorChainElementTest extends AbstractWHCalculatorChainElementTest
{
    /**
     * @var MultipleWHCalculatorChainElement
     */
    protected $multipleWHCalculatorChainElement;

    protected function setUp(): void
    {
        $this->multipleWHCalculatorChainElement = new MultipleWHCalculatorChainElement();
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
        $actualResults = $this->multipleWHCalculatorChainElement->calculate(
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
            [
                'product' => $this->mockProduct(1, [1 => 5, 2 => 5, 3 => 5], [1 => 5, 2 => 4, 3 => 3]),
                'quantity' => 3
            ]
        );
        $orderItem1_2 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(2, [1 => 5, 2 => 5, 3 => 5], [1 => 5, 2 => 4, 3 => 3]),
                'quantity' => 5
            ]
        );
        $orderItem1_3 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(3, [1 => 5, 2 => 5, 3 => 5], [1 => 5, 2 => 4, 3 => 3]),
                'quantity' => 5
            ]
        );
        $orderItems1 = new ArrayCollection([$orderItem1_1, $orderItem1_2, $orderItem1_3]);

        $orderItem3_1 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(1, [2 => 5, 3 => 5, 4 => 5], [2 => 5, 3 => 1, 4 => 3]),
                'quantity' => 3
            ]
        );
        $orderItem3_2 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(2, [2 => 5, 3 => 5, 4 => 5], [2 => 5, 3 => 1, 4 => 3]),
                'quantity' => 5
            ]
        );
        $orderItem3_3 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(5, [2 => 5, 3 => 5, 4 => 5], [2 => 5, 3 => 1, 4 => 3]),
                'quantity' => 3
            ]
        );
        $orderItems3 = new ArrayCollection([$orderItem3_1, $orderItem3_2, $orderItem3_3]);

        $orderItem4_1 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(1, [3 => 5, 4 => 0, 5 => 0], [3 => 5, 4 => 2, 5 => 1]),
                'quantity' => 3
            ]
        );
        $orderItem4_2 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(2, [3 => 5, 4 => 0, 5 => 0], [3 => 5, 4 => 2, 5 => 1]),
                'quantity' => 5
            ]
        );
        $orderItem4_3 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(3, [3 => 5, 4 => 0, 5 => 0], [3 => 5, 4 => 2, 5 => 1]),
                'quantity' => 5
            ]
        );
        $orderItems4 = new ArrayCollection([$orderItem4_1, $orderItem4_2]);

        $orderItem5_1 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(1, [3 => 5, 4 => 5, 5 => 5], [3 => 1, 4 => 2, 5 => 3]),
                'quantity' => 3
            ]
        );
        $orderItem5_2 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(2, [3 => 5, 4 => 5, 5 => 5], [3 => 1, 4 => 2, 5 => 3]),
                'quantity' => 5
            ]
        );
        $orderItem5_3 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(3, [3 => 5, 4 => 5, 5 => 5], [3 => 1, 4 => 2, 5 => 3]),
                'quantity' => 5
            ]
        );
        $orderItems5 = new ArrayCollection([$orderItem5_1, $orderItem5_2, $orderItem5_3]);

        $orderItem6_1 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(1, [3 => 0, 4 => 0, 5 => 0], [3 => 1, 4 => 2, 5 => 3]),
                'quantity' => 3
            ]
        );
        $orderItem6_2 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(1, [3 => 0, 4 => 0, 5 => 0], [3 => 1, 4 => 2, 5 => 3]),
                'quantity' => 5
            ]
        );
        $orderItem6_3 = $this->getEntity(
            OrderItem::class,
            [
                'product' => $this->mockProduct(1, [3 => 0, 4 => 0, 5 => 0], [3 => 1, 4 => 2, 5 => 3]),
                'quantity' => 5
            ]
        );
        $orderItems6 = new ArrayCollection([$orderItem6_1, $orderItem6_2, $orderItem6_3]);
        return [
            'DefaultAndNotDefault' => [
                'productsByWh' => [
                    1 => ['TPD0001', 'TPD0003'],
                    2 => ['TPD0001', 'TPD0002'],
                    3 => ['TPD0002', 'TPD0003']
                ],
                'orderItemsByProducts' => [
                    'TPD0001_|_' => $orderItem1_1,
                    'TPD0002_|_' => $orderItem1_2,
                    'TPD0003_|_' => $orderItem1_3
                ],
                'warehouses' => $this->getWarehouses(),
                'orderItems' => $orderItems1,
                'expectedResults' => [
                    [
                        'TPD0001|TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[1],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem1_1,
                                $orderItem1_3
                            ])
                        ]),
                        'TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[2],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem1_2])
                        ])
                    ],
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[2],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem1_1,
                                $orderItem1_2
                            ])
                        ]),
                        'TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[1],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem1_3])
                        ])
                    ],
                    [
                        'TPD0002|TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem1_2,
                                $orderItem1_3
                            ])
                        ]),
                        'TPD0001' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[1],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem1_1])
                        ])
                    ],
                ]
            ],
            'NotDefaultAndExternal' => [
                'productsByWh' => [
                    2 => ['TPD0001', 'TPD0003'],
                    3 => ['TPD0001', 'TPD0002'],
                    4 => ['TPD0002', 'TPD0003']
                ],
                'orderItemsByProducts' => [
                    'TPD0001_|_' => $orderItem3_1,
                    'TPD0002_|_' => $orderItem3_2,
                    'TPD0003_|_' => $orderItem3_3
                ],
                'warehouses' => $this->getWarehouses(),
                'orderItems' => $orderItems3,
                'expectedResults' => [
                    [
                        'TPD0001|TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[2],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem3_1,
                                $orderItem3_3
                            ])
                        ]),
                        'TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem3_2])
                        ])
                    ],
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem3_1,
                                $orderItem3_2
                            ])
                        ]),
                        'TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[2],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem3_3])
                        ])
                    ],
                    [
                        'TPD0002|TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[4],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem3_2,
                                $orderItem3_3
                            ])
                        ]),
                        'TPD0001' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[2],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem3_1])
                        ])
                    ],
                ]
            ],
            'ExternalWithQtyAndExternalWithoutQty' => [
                'productsByWh' => [
                    3 => ['TPD0001', 'TPD0003'],
                    4 => ['TPD0001', 'TPD0002'],
                    5 => ['TPD0002', 'TPD0003']
                ],
                'orderItemsByProducts' => [
                    'TPD0001_|_' => $orderItem4_1,
                    'TPD0002_|_' => $orderItem4_2,
                    'TPD0003_|_' => $orderItem4_3
                ],
                'warehouses' => $this->getWarehouses(),
                'orderItems' => $orderItems4,
                'expectedResults' => [
                    [
                        'TPD0001|TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem4_1,
                                $orderItem4_3
                            ])
                        ]),
                        'TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[4],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem4_2])
                        ])
                    ],
                    [
                        'TPD0002|TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[5],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem4_2,
                                $orderItem4_3
                            ])
                        ]),
                        'TPD0001' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem4_1])
                        ])
                    ],
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[4],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem4_1,
                                $orderItem4_2
                            ])
                        ]),
                        'TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem4_3])
                        ])
                    ],
                ]
            ],
            'ExternalWithQtyByPrioriries' => [
                'productsByWh' => [
                    3 => ['TPD0001', 'TPD0003'],
                    4 => ['TPD0001', 'TPD0002'],
                    5 => ['TPD0002', 'TPD0003']
                ],
                'orderItemsByProducts' => [
                    'TPD0001_|_' => $orderItem5_1,
                    'TPD0002_|_' => $orderItem5_2,
                    'TPD0003_|_' => $orderItem5_3
                ],
                'warehouses' => $this->getWarehouses(),
                'orderItems' => $orderItems5,
                'expectedResults' => [
                    [
                        'TPD0001|TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem5_1,
                                $orderItem5_3
                            ])
                        ]),
                        'TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[4],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem5_2])
                        ])
                    ],
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[4],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem5_1,
                                $orderItem5_2
                            ])
                        ]),
                        'TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem5_3])
                        ])
                    ],
                    [
                        'TPD0002|TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[5],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem5_2,
                                $orderItem5_3
                            ])
                        ]),
                        'TPD0001' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem5_1])
                        ])
                    ],
                ]
            ],
            'ExternalWithoutQtyByPrioriries' => [
                'productsByWh' => [
                    3 => ['TPD0001', 'TPD0003'],
                    4 => ['TPD0001', 'TPD0002'],
                    5 => ['TPD0002', 'TPD0003']
                ],
                'orderItemsByProducts' => [
                    'TPD0001_|_' => $orderItem6_1,
                    'TPD0002_|_' => $orderItem6_2,
                    'TPD0003_|_' => $orderItem6_3
                ],
                'warehouses' => $this->getWarehouses(),
                'orderItems' => $orderItems6,
                'expectedResults' => [
                    [
                        'TPD0001|TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem6_1,
                                $orderItem6_3
                            ])
                        ]),
                        'TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[4],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem6_2])
                        ])
                    ],
                    [
                        'TPD0001|TPD0002' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[4],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem6_1,
                                $orderItem6_2
                            ])
                        ]),
                        'TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem6_3])
                        ])
                    ],
                    [
                        'TPD0002|TPD0003' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[5],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([
                                $orderItem6_2,
                                $orderItem6_3
                            ])
                        ]),
                        'TPD0001' => new OrderWarehouseResult([
                            OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouses()[3],
                            OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem6_1])
                        ])
                    ],
                ]
            ]
        ];
    }
}
