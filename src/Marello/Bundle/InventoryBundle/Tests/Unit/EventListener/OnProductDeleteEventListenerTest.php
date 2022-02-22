<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\EventListener;

use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Manager\InventoryItemManagerInterface;
use Marello\Bundle\InventoryBundle\EventListener\OnProductDeleteEventListener;

class OnProductDeleteEventListenerTest extends TestCase
{
    /**
     * @var OnProductDeleteEventListener
     */
    protected $listener;

    /**
     * @var InventoryItemManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $inventoryItemManager;

    /**
     * @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityManager;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->entityManager = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->inventoryItemManager = $this
            ->getMockBuilder(InventoryItemManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new OnProductDeleteEventListener($this->inventoryItemManager);
    }

    public function testIfInventoryItemIsDeletedWhenProductIsDeleted()
    {
        $event = $this->prepareEvent();
        $uowMock = $this->createMock(UnitOfWork::class);

        $this->entityManager->expects($this->atLeastOnce())
            ->method('getUnitOfWork')
            ->willReturn($uowMock);

        $productMock = $this->createMock(Product::class);

        $uowMock->expects($this->atLeastOnce())
            ->method('getScheduledEntityDeletions')
            ->willReturn([$productMock]);

        $this->inventoryItemManager->expects($this->atLeastOnce())
            ->method('getInventoryItemToDelete')
            ->with($productMock);

        $this->listener->onFlush($event);
    }

    public function testNoProductsScheduledForDeletion()
    {
        $event = $this->prepareEvent();
        $uowMock = $this->createMock(UnitOfWork::class);

        $this->entityManager->expects($this->atLeastOnce())
            ->method('getUnitOfWork')
            ->willReturn($uowMock);

        $uowMock->expects($this->atLeastOnce())
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);

        $this->inventoryItemManager->expects($this->never())
            ->method('getInventoryItemToDelete');

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
