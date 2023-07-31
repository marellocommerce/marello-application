<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\Event\PostFlushEventArgs;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\WorkflowBundle\Model\WorkflowStartArguments;
use PHPUnit\Framework\TestCase;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Model\WorkflowNameProviderInterface;
use Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener;

class OrderWorkflowStartListenerTest extends TestCase
{
    const TRANSIT_TO_STEP = 'pending';

    /** @var WorkflowManager|\PHPUnit\Framework\MockObject\MockObject $workflowManager */
    private $workflowManagerMock;

    /** @var  OrderWorkflowStartListener $orderWorkflowStartListener */
    private $orderWorkflowStartListener;
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->workflowManagerMock = $this->createMock(WorkflowManager::class);
        $this->orderWorkflowStartListener = new OrderWorkflowStartListener($this->workflowManagerMock);
    }

    /**
     * {@inheritdoc}
     */
    public function testEntityIsNotEligibleToProcess()
    {
        $entity = $this->createMock(OrderItem::class);
        $this->setWorkflowManagerHasApplicableWorkflowsMockExpectation($entity, static::never(), false);
        $this->runPostPersistWorkflowListenerTest($entity, static::never(), null, $this->workflowManagerMock);
        /** @var PostFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $eventPostFlushArgs */
        $eventPostFlushArgs = $this->createMock(PostFlushEventArgs::class);
        $this->workflowManagerMock->expects(static::never())
            ->method('massStartWorkflow');

        $this->orderWorkflowStartListener->postFlush($eventPostFlushArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function testEntityIsEligibleToProcess()
    {
        $entity = $this->createMock(Order::class);
        /** @var Workflow|\PHPUnit\Framework\MockObject\MockObject $workflowMock */
        $workflowMock = $this->createMock(Workflow::class);
        $workflowMock->expects(static::once())
            ->method('getName')
            ->willReturn(WorkflowNameProviderInterface::ORDER_WORKFLOW_1);
        $this->setWorkflowManagerHasApplicableWorkflowsMockExpectation($entity, static::once());
        $this->runPostPersistWorkflowListenerTest(
            $entity,
            static::once(),
            [ WorkflowNameProviderInterface::ORDER_WORKFLOW_1 => $workflowMock ],
            $this->workflowManagerMock
        );

        /** @var PostFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $eventPostFlushArgs */
        $eventPostFlushArgs = $this->createPostFlushEventArgsMock($entity);
        $expectedSchedule = [
            0 =>
                new WorkflowStartArguments(
                    WorkflowNameProviderInterface::ORDER_WORKFLOW_1,
                    $entity,
                    [],
                    'pending'
                )
        ];

        $this->workflowManagerMock->expects(static::once())
            ->method('massStartWorkflow')
            ->with($expectedSchedule);

        $this->orderWorkflowStartListener->postFlush($eventPostFlushArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function testTwoApplicableWorkflowsOnOrderEntity()
    {
        $entity = $this->createMock(Order::class);
        $workflowMock = $this->createMock(Workflow::class);

        $this->setWorkflowManagerHasApplicableWorkflowsMockExpectation($entity, static::once());
        $this->runPostPersistWorkflowListenerTest(
            $entity,
            static::once(),
            [
                WorkflowNameProviderInterface::ORDER_WORKFLOW_1 => $workflowMock,
                WorkflowNameProviderInterface::ORDER_WORKFLOW_2 => $workflowMock
            ],
            $this->workflowManagerMock
        );
        /** @var PostFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $eventPostFlushArgs */
        $eventPostFlushArgs = $this->createPostFlushEventArgsMock($entity);

        $workflowMock->expects(static::never())
            ->method('getName');

        $this->workflowManagerMock->expects(static::never())
            ->method('massStartWorkflow');

        $this->orderWorkflowStartListener->postFlush($eventPostFlushArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function testZeroOrCustomApplicableWorkflowsOnOrderEntity()
    {
        $entity = $this->createMock(Order::class);
        $workflowMock = $this->createMock(Workflow::class);

        $this->setWorkflowManagerHasApplicableWorkflowsMockExpectation($entity, static::once());
        $this->runPostPersistWorkflowListenerTest(
            $entity,
            static::once(),
            [],
            $this->workflowManagerMock
        );

        /** @var PostFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $eventPostFlushArgs */
        $eventPostFlushArgs = $this->createPostFlushEventArgsMock($entity);
        $workflowMock->expects(static::never())
            ->method('getName');

        $this->workflowManagerMock->expects(static::never())
            ->method('massStartWorkflow');

        $this->orderWorkflowStartListener->postFlush($eventPostFlushArgs);
    }

    /**
     * {@inheritdoc}
     * @param $entity|\PHPUnit\Framework\MockObject\MockObject
     * @return LifecycleEventArgs|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createEventArgsMock($entity)
    {
        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $eventArgs->expects(static::once())
            ->method('getObject')
            ->willReturn($entity);

        return $eventArgs;
    }

    /**
     * {@inheritdoc}
     * @param $entity|\PHPUnit\Framework\MockObject\MockObject
     * @param $expected
     * @param null $returnValue
     */
    protected function runPostPersistWorkflowListenerTest(
        $entity, $expected, $returnValue = null, $workflowManagerMock)
    {
        $eventArgs = $this->createEventArgsMock($entity);
        $workflowManagerMock
            ->expects($expected)
            ->method('getApplicableWorkflows')
            ->willReturn($returnValue);

         $this->orderWorkflowStartListener->postPersist($eventArgs);
    }

    /**
     * {@inheritdoc}
     * @param $entity|\PHPUnit\Framework\MockObject\MockObject
     * @return PostFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createPostFlushEventArgsMock($entity)
    {
        $eventPostFlushArgs = $this->createMock(PostFlushEventArgs::class);

        return $eventPostFlushArgs;
    }

    /**
     * {@inheritdoc}
     * @param $entity|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function setWorkflowManagerHasApplicableWorkflowsMockExpectation($entity, $expects, $returnValue = true)
    {
        $this->workflowManagerMock->expects($expects)
            ->method('hasApplicableWorkflows')
            ->with($entity)
            ->willReturn($returnValue);
    }
}
