<?php

namespace Marello\Bundle\PackingBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Mapper\MapperInterface;
use Marello\Bundle\PackingBundle\EventListener\CreatePackingSlipEventListener;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Component\Action\Event\ExtendableActionEvent;

class CreatePackingSlipEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapper;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var CreatePackingSlipEventListener
     */
    protected $createPackingSlipEventListener;

    protected function setUp()
    {
        $this->mapper = $this->createMock(MapperInterface::class);
        /** @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject $doctrineHelper */
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrineHelper
            ->expects(static::once())
            ->method('getEntityManagerForClass')
            ->willReturn($this->entityManager);

        $this->createPackingSlipEventListener = new CreatePackingSlipEventListener(
            $this->mapper,
            $doctrineHelper
        );
    }

    /**
     * @dataProvider onCreatePackingSlipDataProvider
     *
     * @param bool $hasOrder
     * @param array $mappedEntities
     * @param int $persistQuantity
     * @param int $flushQuantity
     */
    public function testOnCreatePackingSlip($hasOrder, array $mappedEntities, $persistQuantity, $flushQuantity)
    {
        $event = $this->prepareEvent($hasOrder);

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
                'hasOrder' => true,
                'mappedEntities' => [new PackingSlip(), new PackingSlip()],
                'persistQuantity' => 2,
                'flushQuantity' => 1
            ],
            'noOrderInContext' => [
                'hasOrder' => false,
                'mappedEntities' => [new PackingSlip(), new PackingSlip()],
                'persistQuantity' => 0,
                'flushQuantity' => 0
            ],
            'noMappedEntities' => [
                'hasOrder' => true,
                'mappedEntities' => [],
                'persistQuantity' => 0,
                'flushQuantity' => 0
            ],
        ];
    }

    /**
     * @param bool $hasOrder
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareEvent($hasOrder = true)
    {
        $event = $this->getMockBuilder(ExtendableActionEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $workflowItem = $this->createMock(WorkflowItem::class);
        $workflowData = $this->createMock(WorkflowData::class);

        $workflowData->expects($this->once())
            ->method('has')
            ->with('order')
            ->willReturn($hasOrder);
        $workflowData->expects($this->any())
            ->method('get')
            ->with('order')
            ->willReturn(new Order());
        $workflowItem->expects($this->any())
            ->method('getData')
            ->willReturn($workflowData);
        $event->expects($this->any())
            ->method('getContext')
            ->willReturn($workflowItem);

        return $event;
    }
}
