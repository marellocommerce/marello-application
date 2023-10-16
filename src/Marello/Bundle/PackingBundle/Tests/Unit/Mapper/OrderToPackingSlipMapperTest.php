<?php

namespace Marello\Bundle\PackingBundle\Tests\Unit\Mapper;

use Doctrine\Common\Collections\ArrayCollection;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;

class OrderToPackingSlipMapperTest extends TestCase
{
    use EntityTrait;

    /**
     * @var EntityFieldProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityFieldProvider;

    /**
     * @var OrderToPackingSlipMapper
     */
    protected $orderToPackingSlipMapper;

    protected function setUp(): void
    {
        $this->entityFieldProvider = $this->getMockBuilder(EntityFieldProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderToPackingSlipMapper = new OrderToPackingSlipMapper(
            $this->entityFieldProvider,
            PropertyAccess::createPropertyAccessor()
        );
    }

    public function testMap()
    {
        $warehouse = new Warehouse();
        
        $this->entityFieldProvider->expects($this->exactly(2))
            ->method('getEntityFields')
            ->willReturnOnConsecutiveCalls(
                [
                    ['name' => 'id', 'identifier' => true],
                    ['name' => 'salesChannel'],
                    ['name' => 'customer'],
                    ['name' => 'organization'],
                    ['name' => 'paymentTerm'],
                    ['name' => 'shippingAddress'],
                    ['name' => 'billingAddress'],
                    ['name' => 'items'],
                ],
                [
                    ['name' => 'id', 'identifier' => true],
                    ['name' => 'product'],
                    ['name' => 'productName'],
                    ['name' => 'productSKU'],
                    ['name' => 'quantity'],
                ]
        );
        $billingAddress = new MarelloAddress();
        $shippingAddress = new MarelloAddress();
        $salesChannel = new SalesChannel();
        $customer = new Customer();
        $organization = new Organization();

        $product1 = $this->getEntity(Product::class, ['id' => 1, 'weight' => 2]);
        $product2 = $this->getEntity(Product::class, ['id' => 2, 'weight' => 3]);
        $product3 = $this->getEntity(Product::class, ['id' => 3, 'weight' => 5]);

        $inventoryLevel1 = $this->getEntity(InventoryLevel::class, ['id' => 1, 'warehouse' => $warehouse]);
        $inventoryBatch1 = $this->getEntity(InventoryBatch::class, ['id' => 1, 'batchNumber' => '000001', 'quantity' => 5]);
        $inventoryItem1 = new InventoryItem($product1);
        $inventoryLevel1->addInventoryBatch($inventoryBatch1);
        $inventoryItem1->addInventoryLevel($inventoryLevel1);

        $orderItem1 = $this->getEntity(
            OrderItem::class,
            [
                'id' => 1,
                'product' => $product1,
                'quantity' => 5,
                'organization' => $organization
            ]
        );
        $orderItem2 = $this->getEntity(
            OrderItem::class,
            [
                'id' => 2,
                'product' => $product2,
                'quantity' => 3,
                'organization' => $organization
            ]
        );
        $orderItem3 = $this->getEntity(
            OrderItem::class,
            [
                'id' => 3,
                'product' => $product3,
                'quantity' => 1,
                'organization' => $organization
            ]
        );

        $order = $this->getEntity(Order::class, [
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress,
            'salesChannel' => $salesChannel,
            'customer' => $customer,
            'organization' => $organization,
            'items' => new ArrayCollection([$orderItem1, $orderItem2, $orderItem3])
        ]);

        $alloItem1 = $this->getEntity(
            AllocationItem::class,
            [
                'id' => 1,
                'product' => $product1,
                'orderItem' => $orderItem1,
                'quantity' => 5,
                'organization' => $organization
            ]
        );
        $alloItem2 = $this->getEntity(
            AllocationItem::class,
            [
                'id' => 2,
                'product' => $product2,
                'orderItem' => $orderItem2,
                'quantity' => 3,
                'organization' => $organization
            ]
        );
        $alloItem3 = $this->getEntity(
            AllocationItem::class,
            [
                'id' => 3,
                'product' => $product3,
                'orderItem' => $orderItem3,
                'quantity' => 1,
                'organization' => $organization
            ]
        );

        $sourceEntity = $this->getEntity(Allocation::class, [
            'shippingAddress' => $shippingAddress,
            'order' => $order,
            'organization' => $organization,
            'items' => new ArrayCollection([$alloItem1, $alloItem2, $alloItem3]),
            'warehouse' => $warehouse
        ]);

        $expectedItems = [
            $this->getEntity(PackingSlipItem::class, [
                'orderItem' => $orderItem1,
                'product' => $product1,
                'inventoryBatches' => ['000001' => 5],
                'quantity' => $orderItem1->getQuantity(),
                'weight' => ($orderItem1->getQuantity() * $product1->getWeight())
            ]),
            $this->getEntity(PackingSlipItem::class, [
                'orderItem' => $orderItem2,
                'product' => $product2,
                'quantity' => $orderItem2->getQuantity(),
                'weight' => ($orderItem2->getQuantity() * $product2->getWeight())
            ]),
            $this->getEntity(PackingSlipItem::class, [
                'orderItem' => $orderItem3,
                'product' => $product3,
                'quantity' => $orderItem3->getQuantity(),
                'weight' => ($orderItem3->getQuantity() * $product3->getWeight())
            ]),
        ];

        $expectedEntity = $this->getEntity(PackingSlip::class, [
            'order' => $order,
            'sourceEntity' => $sourceEntity,
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress,
            'salesChannel' => $salesChannel,
            'customer' => $customer,
            'organization' => $organization,
            'items' => $expectedItems,
            'warehouse' => $warehouse
        ]);

        static::assertEquals([$expectedEntity], $result = $this->orderToPackingSlipMapper->map($sourceEntity));
    }
}
