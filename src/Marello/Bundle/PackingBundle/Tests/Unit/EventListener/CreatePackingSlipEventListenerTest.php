<?php

namespace Marello\Bundle\PackingBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use PHPUnit\Framework\TestCase;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Component\Action\Event\ExtendableActionEvent;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Mapper\MapperInterface;
use Marello\Bundle\PackingBundle\EventListener\CreatePackingSlipEventListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreatePackingSlipEventListenerTest extends TestCase
{
    /**
     * @var MapperInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $mapper;

    /**
     * @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityManager;
    
    /**
     * @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventDispatcher;

    /**
     * @var CreatePackingSlipEventListener
     */
    protected $createPackingSlipEventListener;

    protected function setUp(): void
    {
        $this->mapper = $this->createMock(MapperInterface::class);
        /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelper */
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrineHelper
            ->expects(static::once())
            ->method('getEntityManagerForClass')
            ->willReturn($this->entityManager);

        $this->createPackingSlipEventListener = new CreatePackingSlipEventListener(
            $this->mapper,
            $doctrineHelper,
            $this->eventDispatcher
        );
    }

    /**
     * @dataProvider onCreatePackingSlipDataProvider
     *
     * @param bool $hasAllocation
     * @param array $mappedEntities
     * @param int $persistQuantity
     * @param int $flushQuantity
     */
    public function testOnCreatePackingSlip($hasAllocation, array $mappedEntities, $persistQuantity, $flushQuantity)
    {
        $event = $this->prepareEvent($hasAllocation);

        $this->mapper
            ->expects(static::any())
            ->method('map')
            ->willReturn($mappedEntities);

        $this->entityManager
            ->expects(static::exactly($persistQuantity))
            ->method('persist');
        $this->entityManager
            ->expects(static::exactly($flushQuantity))
            ->method('flush');

        $this->createPackingSlipEventListener->onCreatePackingSlip($event);
    }

    public function onCreatePackingSlipDataProvider()
    {
        return [
            'correctData' => [
                'hasAllocation' => true,
                'mappedEntities' => [new PackingSlip(), new PackingSlip()],
                'persistQuantity' => 2,
                'flushQuantity' => 1
            ],
            'noOrderInContext' => [
                'hasAllocation' => false,
                'mappedEntities' => [new PackingSlip(), new PackingSlip()],
                'persistQuantity' => 0,
                'flushQuantity' => 0
            ],
            'noMappedEntities' => [
                'hasAllocation' => true,
                'mappedEntities' => [],
                'persistQuantity' => 0,
                'flushQuantity' => 0
            ],
        ];
    }

    /**
     * @param bool $hasAllocation
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function prepareEvent($hasAllocation = true)
    {
        $event = $this->getMockBuilder(ExtendableActionEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $workflowItem = $this->createMock(WorkflowItem::class);
        $workflowData = $this->createMock(WorkflowData::class);
        $allocation = new Allocation();
        $allocation->setOrder(new Order());
        $workflowData->expects($this->once())
            ->method('has')
            ->with('allocation')
            ->willReturn($hasAllocation);
        $workflowData->expects($this->any())
            ->method('get')
            ->with('allocation')
            ->willReturn($allocation);
        $workflowItem->expects($this->any())
            ->method('getData')
            ->willReturn($workflowData);
        $event->expects($this->any())
            ->method('getContext')
            ->willReturn($workflowItem);

        return $event;
    }
}
