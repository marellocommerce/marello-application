<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class PurchaseOrderOnOrderOnDemandCreationListenerTest extends TestCase
{
    use EntityTrait;

    /**
     * @var AvailableInventoryProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $availableInventoryProvider;

    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configManager;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aclHelper;

    /**
     * @var PurchaseOrderOnOrderOnDemandCreationListener
     */
    protected $purchaseOrderOnOrderOnDemandCreationListener;

    protected function setUp(): void
    {
        $this->availableInventoryProvider = $this->createMock(AvailableInventoryProvider::class);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->configManager->expects($this->any())
            ->method('get')
            ->willReturn(true);
        $this->purchaseOrderOnOrderOnDemandCreationListener =
            new PurchaseOrderOnOrderOnDemandCreationListener($this->availableInventoryProvider, $this->aclHelper);
        $this->purchaseOrderOnOrderOnDemandCreationListener->setConfigManager($this->configManager);
    }

    public function testPostFlush()
    {
        /** @var LifecycleEventArgs|\PHPUnit\Framework\MockObject\MockObject $args **/
        $postPersistArgs = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $postPersistArgs
            ->expects(static::once())
            ->method('getEntity')
            ->willReturn($this->getOrder());
        /** @var PostFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $args **/
        $postFlushArgs = $this->getMockBuilder(PostFlushEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository
            ->expects(static::once())
            ->method('find')
            ->willReturn($this->getOrder());
        $whchgrlinkRepository = $this->getMockBuilder(WarehouseChannelGroupLinkRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $whchgrlinkRepository
            ->expects(static::once())
            ->method('findLinkBySalesChannelGroup')
            ->willReturn($this->getWarehouseChannelGroupLing());
        $manager
            ->expects(static::any())
            ->method('getRepository')
            ->withConsecutive([Order::class], [WarehouseChannelGroupLink::class])
            ->willReturnOnConsecutiveCalls($orderRepository, $whchgrlinkRepository);
        $postFlushArgs
            ->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($manager);
        $manager
            ->expects(static::exactly(1))
            ->method('persist');
        $manager
            ->expects(static::exactly(1))
            ->method('flush');

        $this->purchaseOrderOnOrderOnDemandCreationListener->postPersist($postPersistArgs);
        $this->purchaseOrderOnOrderOnDemandCreationListener->postFlush($postFlushArgs);
    }

    /**
     * @return WarehouseChannelGroupLink
     */
    private function getWarehouseChannelGroupLing()
    {
        /** @var Warehouse $warehouse */
        $warehouse = $this->getEntity(Warehouse::class, ['id' => 1]);
        /** @var WarehouseGroup $warehouseGroup */
        $warehouseGroup = $this->getEntity(WarehouseGroup::class, ['id' => 1]);
        $warehouseGroup->addWarehouse($warehouse);
        /** @var WarehouseChannelGroupLink $link */
        $link = $this->getEntity(WarehouseChannelGroupLink::class, ['id' => 1, 'warehouseGroup' => $warehouseGroup]);

        return $link;
    }

    /**
     * @return Order
     */
    private function getOrder()
    {
        $salesChannelGroup = $this->getEntity(SalesChannelGroup::class, ['id' => 1]);
        $salesChannel = $this->getEntity(SalesChannel::class, ['id' => 1, 'group' => $salesChannelGroup]);

        $organization = $this->getEntity(Organization::class, ['id' => 1]);
        /** @var Order $order */
        $order = $this->getEntity(
            Order::class,
            ['id' => 1, 'salesChannel' => $salesChannel, 'organization' => $organization]
        );


        $product1 = $this->getProduct(1);
        /** @var OrderItem $orderItem1 */
        $orderItem1 = $this->getEntity(OrderItem::class, ['product' => $product1, 'quantity' => 10]);

        $product2 = $this->getProduct(2);
        /** @var OrderItem $orderItem2 */
        $orderItem2 = $this->getEntity(OrderItem::class, ['product' => $product2, 'quantity' => 10]);

        $order
            ->addItem($orderItem1)
            ->addItem($orderItem2);

        return $order;
    }

    /**
     * @param $id
     * @return Product
     */
    private function getProduct($id)
    {
        $supplier = $this->getEntity(
            Supplier::class,
            [
                'id' => $id,
                'name' => sprintf('Supplier%d', $id),
                'currency' => 'USD'
            ]
        );
        /** @var Product $product */
        $product = $this->getEntity(
            Product::class,
            [
                'id' => $id,
                'sku' => sprintf('SKU-%d', $id),
                'preferredSupplier' => $supplier
            ]
        );
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $this->getEntity(
            InventoryItem::class,
            ['id' => $id, 'orderOnDemandAllowed' => true],
            [$product]
        );
        $product->addInventoryItem($inventoryItem);
        /** @var ProductSupplierRelation $productSupplierRelation */
        $productSupplierRelation = $this->getEntity(
            ProductSupplierRelation::class,
            ['id' => $id, 'supplier' => $supplier, 'cost' => 10]
        );
        $product->addSupplier($productSupplierRelation);

        return $product;
    }
}
