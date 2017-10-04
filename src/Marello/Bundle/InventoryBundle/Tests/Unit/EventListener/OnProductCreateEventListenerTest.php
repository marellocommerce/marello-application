<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\InventoryBundle\EventListener\OnProductCreateEventListener;
use Marello\Bundle\InventoryBundle\Manager\InventoryItemManagerInterface;
use Marello\Bundle\ProductBundle\Entity\Product;

class OnProductCreateEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OnProductCreateEventListener
     */
    protected $listener;

    /**
     * @var InventoryItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inventoryItemManager;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->entityManager = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->inventoryItemManager = $this
            ->getMockBuilder(InventoryItemManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new OnProductCreateEventListener($this->inventoryItemManager);
    }

    public function testIfInventoryItemIsCreatedWhenProductIsCreated()
    {
        $event = $this->prepareEvent();
        $uowMock = $this->createMock(UnitOfWork::class);

        $this->entityManager->expects($this->atLeastOnce())
            ->method('getUnitOfWork')
            ->willReturn($uowMock);

        $productMock = $this->createMock(Product::class);

        $uowMock->expects($this->atLeastOnce())
            ->method('getScheduledEntityInsertions')
            ->willReturn([$productMock]);

        $this->inventoryItemManager->expects($this->atLeastOnce())
            ->method('createInventoryItem')
            ->with($productMock);

        $this->listener->onFlush($event);
    }

    public function testNoProductsScheduledForInsert()
    {
        $event = $this->prepareEvent();
        $uowMock = $this->createMock(UnitOfWork::class);

        $this->entityManager->expects($this->atLeastOnce())
            ->method('getUnitOfWork')
            ->willReturn($uowMock);

        $uowMock->expects($this->atLeastOnce())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);

        $this->inventoryItemManager->expects($this->never())
            ->method('createInventoryItem');

        $this->listener->onFlush($event);
    }

    /**
     * @return OnFlushEventArgs
     */
    protected function prepareEvent()
    {
        return new OnFlushEventArgs($this->entityManager);
    }
}
