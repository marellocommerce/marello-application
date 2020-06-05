<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Model\WorkflowNameProviderInterface;
use Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener;

class OrderWorkflowStartListenerTest extends TestCase
{
    const TRANSIT_TO_STEP = 'pending';

    /** @var WorkflowManager|\PHPUnit_Framework_MockObject_MockObject $workflowManager */
    private $workflowManagerMock;

    /** @var  OrderWorkflowStartListener $orderWorkflowStartListener */
    private $orderWorkflowStartListener;

    /** @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject $doctrineHelperMock */
    private $doctrineHelperMock;
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->workflowManagerMock = $this->createMock(WorkflowManager::class);
        $this->doctrineHelperMock = $this->createMock(DoctrineHelper::class);
        $this->orderWorkflowStartListener = new OrderWorkflowStartListener($this->workflowManagerMock);
        $this->orderWorkflowStartListener->setDoctrineHelper($this->doctrineHelperMock);
    }

    /**
     * {@inheritdoc}
     */
    public function testEntityIsNotEligibleToProcess()
    {
        $entity = $this->createMock(OrderItem::class);
        $this->runFirstSequenceOfWorkflowListenerTest($entity, static::never());
        /** @var PostFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $eventPostFlushArgs */
        $eventPostFlushArgs = $this->createMock(PostFlushEventArgs::class);
        $this->doctrineHelperMock->expects(static::never())
            ->method('getEntityManagerForClass');

        $this->orderWorkflowStartListener->postFlush($eventPostFlushArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function testEntityIsEligibleToProcess()
    {
        $entity = $this->createMock(Order::class);
        $this->runFirstSequenceOfWorkflowListenerTest($entity, static::once(), 1);
        /** @var PostFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $eventPostFlushArgs */
        $eventPostFlushArgs = $this->createPostFlushEventArgsMock($entity);
        $this->setWorkflowManagerHasApplicableWorkflowsMockExpectation($entity);

        $workflowMock = $this->createMock(Workflow::class);
        $workflowMock->expects(static::once())
            ->method('getName')
            ->willReturn(WorkflowNameProviderInterface::ORDER_WORKFLOW_1);

        $this->workflowManagerMock->expects(static::once())
            ->method('getApplicableWorkflows')
            ->with($entity)
            ->willReturn([WorkflowNameProviderInterface::ORDER_WORKFLOW_1 => $workflowMock]);

        $this->workflowManagerMock->expects(static::once())
            ->method('startWorkflow')
            ->with(
                WorkflowNameProviderInterface::ORDER_WORKFLOW_1,
                $entity,
                OrderWorkflowStartListener::TRANSIT_TO_STEP
            );

        $this->orderWorkflowStartListener->postFlush($eventPostFlushArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function testTwoApplicableWorkflowsOnOrderEntity()
    {
        $entity = $this->createMock(Order::class);
        $this->runFirstSequenceOfWorkflowListenerTest($entity, static::once(), 1);
        /** @var PostFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $eventPostFlushArgs */
        $eventPostFlushArgs = $this->createPostFlushEventArgsMock($entity);
        $this->setWorkflowManagerHasApplicableWorkflowsMockExpectation($entity);

        $workflowMock = $this->createMock(Workflow::class);
        $this->workflowManagerMock->expects(static::once())
            ->method('getApplicableWorkflows')
            ->with($entity)
            ->willReturn(
                [
                    WorkflowNameProviderInterface::ORDER_WORKFLOW_1 => $workflowMock,
                    WorkflowNameProviderInterface::ORDER_WORKFLOW_2 => $workflowMock
                ]
            );

        $workflowMock->expects(static::never())
            ->method('getName');

        $this->workflowManagerMock->expects(static::never())
            ->method('startWorkflow');

        $this->orderWorkflowStartListener->postFlush($eventPostFlushArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function testZeroOrCustomApplicableWorkflowsOnOrderEntity()
    {
        $entity = $this->createMock(Order::class);
        $this->runFirstSequenceOfWorkflowListenerTest($entity, static::once(), 1);
        /** @var PostFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $eventPostFlushArgs */
        $eventPostFlushArgs = $this->createPostFlushEventArgsMock($entity);
        $this->setWorkflowManagerHasApplicableWorkflowsMockExpectation($entity);

        $this->workflowManagerMock->expects(static::once())
            ->method('getApplicableWorkflows')
            ->with($entity)
            ->willReturn([]);

        $workflowMock = $this->createMock(Workflow::class);
        $workflowMock->expects(static::never())
            ->method('getName');

        $this->workflowManagerMock->expects(static::never())
            ->method('startWorkflow');

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
            ->method('getEntity')
            ->willReturn($entity);

        return $eventArgs;
    }

    /**
     * {@inheritdoc}
     * @param $entity|\PHPUnit\Framework\MockObject\MockObject
     * @param $expected
     * @param null $returnValue
     */
    protected function runFirstSequenceOfWorkflowListenerTest($entity, $expected, $returnValue = null)
    {
        $eventArgs = $this->createEventArgsMock($entity);
        $entity
            ->expects($expected)
            ->method('getId')
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
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->doctrineHelperMock->expects(static::once())
            ->method('getEntityManagerForClass')
            ->willReturn($entityManager);

        $entityRepository = $this->createMock(EntityRepository::class);
        $entityManager->expects(static::once())
            ->method('getRepository')
            ->willReturn($entityRepository);

        $entityRepository->expects(static::once())
            ->method('find')
            ->with(1)
            ->willReturn($entity);

        return $eventPostFlushArgs;
    }

    /**
     * {@inheritdoc}
     * @param $entity|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function setWorkflowManagerHasApplicableWorkflowsMockExpectation($entity)
    {
        $this->workflowManagerMock->expects(static::once())
            ->method('hasApplicableWorkflows')
            ->with($entity)
            ->willReturn(true);
    }
}
