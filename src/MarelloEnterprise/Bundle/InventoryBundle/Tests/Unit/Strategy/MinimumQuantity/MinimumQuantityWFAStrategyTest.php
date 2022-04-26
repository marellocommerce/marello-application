<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumQuantity;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
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
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\MinimumQuantityWFAStrategy;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\MinQtyWHCalculatorInterface;

class MinimumQuantityWFAStrategyTest extends TestCase
{
    use EntityTrait;

    /**
     * @var MinQtyWHCalculatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $minQtyWHCalculator;

    /**
     * @var MinimumQuantityWFAStrategy
     */
    protected $minimumQuantityWFAStrategy;

    /**
     * @var WarehouseChannelGroupLinkRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $warehouseChannelGroupLinkRepository;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aclHelper;

    protected function setUp(): void
    {
        $this->minQtyWHCalculator = $this->createMock(MinQtyWHCalculatorInterface::class);
        $this->warehouseChannelGroupLinkRepository = $this->createMock(WarehouseChannelGroupLinkRepository::class);
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->any())
            ->method('getRepository')
            ->with(WarehouseChannelGroupLink::class)
            ->willReturn($this->warehouseChannelGroupLinkRepository);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->minimumQuantityWFAStrategy = new MinimumQuantityWFAStrategy(
            $this->minQtyWHCalculator,
            $registry,
            $this->aclHelper
        );
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
        
        $warehouse1 = $this->getEntity(Warehouse::class, [
            'id' => 1,
            'default' => true,
            'warehouseType' => new WarehouseType(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL)
        ]);
        $warehouse2 = $this->getEntity(Warehouse::class, [
            'id' => 2,
            'warehouseType' => new WarehouseType(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_VIRTUAL)
        ]);
        $warehouse3 = $this->getEntity(Warehouse::class, [
            'id' => 3,
            'warehouseType' => new WarehouseType(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL)
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

        $product1->addInventoryItem($inventoryItem1);
        $product2->addInventoryItem($inventoryItem2);
        $product3->addInventoryItem($inventoryItem3);

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
            1 => ['TPD0001', 'TPD0003'],
            2 => ['TPD0001', 'TPD0002'],
            3 => ['TPD0002', 'TPD0003']
        ];
        $orderItemsByProducts = [
            'TPD0001_|_0' => $orderItem1,
            'TPD0002_|_1' => $orderItem2,
            'TPD0003_|_2' => $orderItem3,
        ];
        $warehouses = [
            1 => $warehouse1,
            2 => $warehouse2,
            3 => $warehouse3,
        ];


        $warehouseGroup = $this->getEntity(
            WarehouseGroup::class,
            [
                'id' => 1,
                'warehouses' => $warehouses
            ]
        );
        $warehouseGroupLink = $this->getEntity(
            WarehouseChannelGroupLink::class,
            [
                'warehouseGroup' => $warehouseGroup,
                'salesChannelGroups' => [$salesChannelGroup]
            ]
        );

        $this->warehouseChannelGroupLinkRepository
            ->expects(static::once())
            ->method('findLinkBySalesChannelGroup')
            ->with($salesChannelGroup)
            ->willReturn($warehouseGroupLink);

        $this->minQtyWHCalculator
            ->expects(static::once())
            ->method('calculate')
            ->with($productsByWh, $orderItemsByProducts, $warehouses, $order->getItems());

        $this->minimumQuantityWFAStrategy->getWarehouseResults($order, $initialResults);
    }
}
