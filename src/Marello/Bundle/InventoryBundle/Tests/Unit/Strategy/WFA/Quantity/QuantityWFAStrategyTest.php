<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\WFA\Quantity;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\AllocationExclusionProvider;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTrait;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository;
use Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy;
use Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator\QtyWHCalculatorInterface;

class QuantityWFAStrategyTest extends TestCase
{
    use EntityTrait;

    /**
     * @var QtyWHCalculatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $qtyWHCalculator;

    /**
     * @var QuantityWFAStrategy
     */
    protected $quantityWFAStrategy;

    /**
     * @var WarehouseChannelGroupLinkRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $warehouseChannelGroupLinkRepository;

    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configManager;

    protected function setUp(): void
    {
        $this->qtyWHCalculator = $this->createMock(QtyWHCalculatorInterface::class);
        $this->warehouseChannelGroupLinkRepository = $this->createMock(WarehouseChannelGroupLinkRepository::class);
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->any())
            ->method('getRepository')
            ->with(WarehouseChannelGroupLink::class)
            ->willReturn($this->warehouseChannelGroupLinkRepository);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->quantityWFAStrategy = new QuantityWFAStrategy(
            $this->qtyWHCalculator,
            $this->warehouseChannelGroupLinkRepository,
            $this->configManager
        );
    }

    public function testGetIdentifier()
    {
        static::assertEquals(
            QuantityWFAStrategy::IDENTIFIER,
            $this->quantityWFAStrategy->getIdentifier()
        );
    }

    public function testGetLabel()
    {
        static::assertEquals(
            QuantityWFAStrategy::LABEL,
            $this->quantityWFAStrategy->getLabel()
        );
    }

    public function testIsEnabled()
    {
        static::assertEquals(
            true,
            $this->quantityWFAStrategy->isEnabled()
        );
    }

    public function testGetWarehouseResults()
    {
        $product1 = $this->getEntity(Product::class, ['sku' => 'TPD0001']);
        $product2 = $this->getEntity(Product::class, ['sku' => 'TPD0002']);
        $product3 = $this->getEntity(Product::class, ['sku' => 'TPD0003']);
        
        $warehouse1 = $this->getEntity(Warehouse::class, [
            'id' => 1,
            'default' => true,
            'warehouseType' => new WarehouseType(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL),
            'code' => 'warehouse1',
        ]);
        $warehouse2 = $this->getEntity(Warehouse::class, [
            'id' => 2,
            'warehouseType' => new WarehouseType(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_VIRTUAL),
            'code' => 'warehouse2',
        ]);
        $warehouse3 = $this->getEntity(Warehouse::class, [
            'id' => 3,
            'warehouseType' => new WarehouseType(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL),
            'code' => 'warehouse3',
        ]);
        $noAllocationWarehouse = $this->getEntity(Warehouse::class, [
            'id' => null,
            'warehouseType' => new WarehouseType(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_VIRTUAL),
            'code' => QuantityWFAStrategy::CNA_WAREHOUSE_CODE,
        ]);

        $inventoryLevel1 = $this->getEntity(InventoryLevel::class, ['inventory' => 10, 'warehouse' => $warehouse1]);
        $inventoryLevel2 = $this->getEntity(InventoryLevel::class, ['inventory' => 10, 'warehouse' => $warehouse2]);
        $inventoryLevel3 = $this->getEntity(InventoryLevel::class, ['inventory' => 0, 'warehouse' => $warehouse3]);

        $inventoryItem1 = $this->getEntity(
            InventoryItem::class,
            [
                'inventoryLevels' => [$inventoryLevel1, $inventoryLevel2]
            ],
            [
                $product1
            ]
        );
        $inventoryItem2 = $this->getEntity(
            InventoryItem::class,
            [
                'inventoryLevels' => [$inventoryLevel2, $inventoryLevel3]
            ],
            [
                $product2
            ]
        );
        $inventoryItem3 = $this->getEntity(
            InventoryItem::class,
            [
                'inventoryLevels' => [$inventoryLevel1, $inventoryLevel3]
            ],
            [
                $product3
            ]
        );

        $product1->setInventoryItem($inventoryItem1);
        $product2->setInventoryItem($inventoryItem2);
        $product3->setInventoryItem($inventoryItem3);

        $orderItem1 = $this->getEntity(
            OrderItem::class,
            ['productSku' => $product1->getSku(), 'product' => $product1, 'quantity' => 1]
        );
        $orderItem2 = $this->getEntity(
            OrderItem::class,
            ['productSku' => $product2->getSku(), 'product' => $product2, 'quantity' => 1]
        );
        $orderItem3 = $this->getEntity(
            OrderItem::class,
            ['productSku' => $product3->getSku(), 'product' => $product3, 'quantity' => 1]
        );

        $salesChannelGroup = $this->getEntity(SalesChannelGroup::class, ['id' => 1]);
        $salesChannel = $this->getEntity(SalesChannel::class, ['id' => 1, 'group' => $salesChannelGroup]);

        /** @var Order|\PHPUnit\Framework\MockObject\MockObject $order **/
        $order = $this->getEntity(
            Order::class,
            [
                'items' => [$orderItem1, $orderItem2, $orderItem3],
                'salesChannel' => $salesChannel
            ]
        );
        $initialResults = [];

        $productsByWh = [
            'TPD0001' => [
                [
                    'sku' => 'TPD0001',
                    'wh' => 'warehouse1',
                    'qty' => 1,
                    'qtyOrdered' => 1,
                ]
            ],
            'TPD0002' => [
                [
                    'sku' => 'TPD0002',
                    'wh' => 'warehouse2',
                    'qty' => 1,
                    'qtyOrdered' => 1,
                ]
            ],
            'TPD0003'=> [
                [
                    'sku' => 'TPD0003',
                    'wh' => 'warehouse1',
                    'qty' => 1,
                    'qtyOrdered' => 1,
                ]
            ]
        ];
        $orderItemsByProducts = [
            'TPD0001_|_0' => $orderItem1,
            'TPD0002_|_1' => $orderItem2,
            'TPD0003_|_2' => $orderItem3,
        ];
        $warehouses = [
            $warehouse1->getCode() => $warehouse1,
            $warehouse2->getCode() => $warehouse2,
            $warehouse3->getCode() => $warehouse3,
        ];

        $warehouseGroup = $this->getEntity(
            WarehouseGroup::class,
            [
                'id' => 1,
                'warehouses' => $warehouses,
            ]
        );
        $warehouseGroupLink = $this->getEntity(
            WarehouseChannelGroupLink::class,
            [
                'warehouseGroup' => $warehouseGroup,
                'salesChannelGroups' => [$salesChannelGroup]
            ]
        );
        // add no allocation warehouse
        $warehouses[$noAllocationWarehouse->getCode()] = $noAllocationWarehouse;

        $this->warehouseChannelGroupLinkRepository
            ->expects(static::once())
            ->method('findLinkBySalesChannelGroup')
            ->with($salesChannelGroup)
            ->willReturn($warehouseGroupLink);

        $this->qtyWHCalculator
            ->expects(static::once())
            ->method('calculate')
            ->with($productsByWh, $orderItemsByProducts, $warehouses, $order->getItems())
            ->willReturn([]);

        $allocationExclusionProvider = $this->createMock(AllocationExclusionProvider::class);
        $allocationExclusionProvider
            ->expects(static::once())
            ->method('getItems')
            ->with($order)
            ->willReturn($order->getItems());
        $this->quantityWFAStrategy->setAllocationExclusionProvider($allocationExclusionProvider);

        $this->quantityWFAStrategy->getWarehouseResults($order, null, $initialResults);
    }
}
