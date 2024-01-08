<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Marello\Bundle\InventoryBundle\Provider\AllocationContextInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class PurchaseOrderOnOrderOnDemandCreationListenerTest extends TestCase
{
    use EntityTrait;

    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configManager;

    /**
     * @var PurchaseOrderOnOrderOnDemandCreationListener
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->configManager->expects($this->any())
            ->method('get')
            ->willReturn(true);
        $this->listener = new PurchaseOrderOnOrderOnDemandCreationListener();
        $this->listener->setConfigManager($this->configManager);
    }

    public function testPostFlush()
    {
        $allocation = $this->getAllocation();

        /** @var LifecycleEventArgs|\PHPUnit\Framework\MockObject\MockObject $args **/
        $postPersistArgs = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $postPersistArgs
            ->expects(static::once())
            ->method('getObject')
            ->willReturn($allocation);
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
            ->willReturn($this->getAllocation());
        $linkRepository = $this->createMock(WarehouseChannelGroupLinkRepository::class);
        $whGroup = new WarehouseGroup();
        $groupLink = new WarehouseChannelGroupLink();
        $groupLink->setWarehouseGroup($whGroup);
        $linkRepository
            ->expects(static::exactly(2))
            ->method('findLinkBySalesChannelGroup')
            ->with($allocation->getOrder()->getSalesChannel()->getGroup())
            ->willReturn($groupLink);
        $whRepository = $this->createMock(WarehouseRepository::class);
        $manager
            ->expects(static::any())
            ->method('getRepository')
            ->withConsecutive(
                [Allocation::class],
                [WarehouseChannelGroupLink::class],
                [Warehouse::class],
                [WarehouseChannelGroupLink::class],
                [Warehouse::class]
            )
            ->willReturnOnConsecutiveCalls(
                $orderRepository,
                $linkRepository,
                $whRepository,
                $linkRepository,
                $whRepository
            );
        $postFlushArgs
            ->expects(static::once())
            ->method('getObjectManager')
            ->willReturn($manager);
        $manager
            ->expects(static::any())
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                $reflectionProperty = new \ReflectionProperty(get_class($entity), 'id');
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($entity, random_int(0, 10));
            });
        $manager
            ->expects(static::once())
            ->method('flush');

        $this->listener->postPersist($postPersistArgs);
        $this->listener->postFlush($postFlushArgs);
    }

    private function getAllocation(): Allocation
    {
        $salesChannelGroup = $this->getEntity(SalesChannelGroup::class, ['id' => 1]);
        $salesChannel = $this->getEntity(SalesChannel::class, ['id' => 1, 'group' => $salesChannelGroup]);

        $organization = $this->getEntity(Organization::class, ['id' => 1]);
        $state = new TestEnumValue(
            AllocationStateStatusInterface::ALLOCATION_STATE_WFS,
            AllocationStateStatusInterface::ALLOCATION_STATE_WFS
        );
        $context = new TestEnumValue(
            AllocationContextInterface::ALLOCATION_CONTEXT_ORDER,
            AllocationContextInterface::ALLOCATION_CONTEXT_ORDER
        );
        /** @var Order $order */
        $order = $this->getEntity(
            Order::class,
            ['id' => 1, 'salesChannel' => $salesChannel, 'organization' => $organization]
        );
        /** @var Allocation $order */
        $allocation = $this->getEntity(
            Allocation::class,
            ['id' => 1, 'state' => $state, 'allocationContext' => $context, 'order' => $order, 'organization' => $organization]
        );

        $product1 = $this->getProduct(1);
        $orderItem1 = $this->getEntity(OrderItem::class, ['product' => $product1]);
        /** @var AllocationItem $item1 */
        $item1 = $this->getEntity(
            AllocationItem::class,
            [
                'product' => $product1,
                'quantity' => 10,
                'orderItem' => $orderItem1,
            ]
        );

        $product2 = $this->getProduct(2);
        $orderItem2 = $this->getEntity(OrderItem::class, ['product' => $product2]);
        /** @var AllocationItem $item2 */
        $item2 = $this->getEntity(
            AllocationItem::class,
            [
                'product' => $product2,
                'quantity' => 10,
                'orderItem' => $orderItem2,
            ]
        );

        $allocation
            ->addItem($item1)
            ->addItem($item2);

        return $allocation;
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
            ['id' => $id, 'orderOnDemandAllowed' => true, 'enableBatchInventory' => true],
            [$product]
        );
        $product->setInventoryItem($inventoryItem);
        /** @var ProductSupplierRelation $productSupplierRelation */
        $productSupplierRelation = $this->getEntity(
            ProductSupplierRelation::class,
            ['id' => $id, 'supplier' => $supplier, 'cost' => 10]
        );
        $product->addSupplier($productSupplierRelation);

        return $product;
    }
}
