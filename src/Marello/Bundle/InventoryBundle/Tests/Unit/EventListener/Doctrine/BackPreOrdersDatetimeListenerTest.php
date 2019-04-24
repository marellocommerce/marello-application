<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\EventListener\Doctrine\BackPreOrdersDatetimeListener;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\Event\LifecycleEventArgs;

class BackPreOrdersDatetimeListenerTest extends TestCase
{
    /**
     * @var BackPreOrdersDatetimeListener
     */
    private $listener;

    /**
     * @var FeatureChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $featureChecker;

    /**
     * @var InventoryItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inventoryItem;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->featureChecker = $this->createMock(FeatureChecker::class);

        $this->listener = new BackPreOrdersDatetimeListener();
        $this->listener->setFeatureChecker($this->featureChecker);

        $this->inventoryItem = $this->createMock(InventoryItem::class);
    }

    public function testWithDueDateAndEnabledFeature()
    {
        $args = $this->createMock(LifecycleEventArgs::class);
        $em = $this->createMock(EntityManager::class);
        $uow = $this->createMock(UnitOfWork::class);
        $args
            ->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($em);
        $em
            ->expects(static::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $this->featureChecker
            ->expects(static::once())
            ->method('isFeatureEnabled')
            ->with('po_duedate_as_back_pre_orders_datetime')
            ->willReturn(true);
        $po = $this->mockPurchaseOrder();
        $po
            ->expects(static::any())
            ->method('getDueDate')
            ->willReturn(new \DateTime());
        $this->inventoryItem
            ->expects(static::once())
            ->method('setBackPreOrdersDatetime');
        $uow
            ->expects(static::once())
            ->method('scheduleForUpdate')
            ->with($this->inventoryItem);
        
        $this->listener->postPersist($po, $args);
    }

    public function testWithDueDateAndDisabledFeature()
    {
        $args = $this->createMock(LifecycleEventArgs::class);
        $em = $this->createMock(EntityManager::class);
        $uow = $this->createMock(UnitOfWork::class);
        $args
            ->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($em);
        $em
            ->expects(static::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $this->featureChecker
            ->expects(static::once())
            ->method('isFeatureEnabled')
            ->with('po_duedate_as_back_pre_orders_datetime')
            ->willReturn(false);
        $po = $this->mockPurchaseOrder();
        $po
            ->expects(static::any())
            ->method('getDueDate')
            ->willReturn(new \DateTime());
        $this->inventoryItem
            ->expects(static::never())
            ->method('setBackPreOrdersDatetime');
        $uow
            ->expects(static::never())
            ->method('scheduleForUpdate')
            ->with($this->inventoryItem);

        $this->listener->postPersist($po, $args);
    }

    public function testNoDueDateAndEnabledFeature()
    {
        $args = $this->createMock(LifecycleEventArgs::class);
        $em = $this->createMock(EntityManager::class);
        $uow = $this->createMock(UnitOfWork::class);
        $args
            ->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($em);
        $em
            ->expects(static::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $this->featureChecker
            ->expects(static::never())
            ->method('isFeatureEnabled')
            ->with('po_duedate_as_back_pre_orders_datetime')
            ->willReturn(true);
        $po = $this->mockPurchaseOrder();
        $po
            ->expects(static::any())
            ->method('getDueDate')
            ->willReturn(null);
        $this->inventoryItem
            ->expects(static::never())
            ->method('setBackPreOrdersDatetime');
        $uow
            ->expects(static::never())
            ->method('scheduleForUpdate')
            ->with($this->inventoryItem);

        $this->listener->postPersist($po, $args);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function mockPurchaseOrder()
    {
        $product = $this->createMock(PurchaseOrderItem::class);
        $product
            ->expects(static::any())
            ->method('getInventoryItems')
            ->willReturn(new ArrayCollection([$this->inventoryItem]));
        $poItem = $this->createMock(PurchaseOrderItem::class);
        $poItem
            ->expects(static::any())
            ->method('getProduct')
            ->willReturn($product);
        $po = $this->createMock(PurchaseOrder::class);
        $po
            ->expects(static::any())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$poItem]));

        return $po;
    }
}
