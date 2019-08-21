<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use PHPUnit\Framework\TestCase;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener;

class OrderWorkflowStartListenerTest extends TestCase
{
    const WORKFLOW = 'marello_order_b2c_workflow_1';
    const WORKFLOW_2 = 'marello_order_b2c_workflow_2';
    const TRANSIT_TO_STEP = 'pending';

    /** @var WorkflowManager|\PHPUnit_Framework_MockObject_MockObject $workflowManager */
    private $workflowManagerMock;

    /** @var  OrderWorkflowStartListener $orderWorkflowStartListener */
    private $orderWorkflowStartListener;

    protected function setUp()
    {
        $this->workflowManagerMock = $this->createMock(WorkflowManager::class);
        $this->orderWorkflowStartListener = new OrderWorkflowStartListener($this->workflowManagerMock);
    }

    /**
     * {@inheritdoc}
     */
    public function testEntityIsNotEligibleToProcess()
    {
        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $entity = $this->createMock(OrderItem::class);
        $eventArgs->expects(static::once())
            ->method('getEntity')
            ->willReturn($entity);

        $entity->expects(static::never())
            ->method('getId');

        $this->orderWorkflowStartListener->postPersist($eventArgs);

        $eventPostFlushArgs = $this->createMock(PostFlushEventArgs::class);
        $eventPostFlushArgs->expects(static::never())
            ->method('getEntityManager');

        $this->orderWorkflowStartListener->postFlush($eventPostFlushArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function testEntityIsEligibleToProcess()
    {
        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $entity = $this->createMock(Order::class);
        $eventArgs->expects(static::once())
            ->method('getEntity')
            ->willReturn($entity);

        $entity->expects(static::once())
            ->method('getId')
            ->willReturn(1);

        $this->orderWorkflowStartListener->postPersist($eventArgs);
        $eventPostFlushArgs = $this->createMock(PostFlushEventArgs::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $eventPostFlushArgs->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $entityRepository = $this->createMock(EntityRepository::class);
        $entityManager->expects(static::once())
            ->method('getRepository')
            ->willReturn($entityRepository);

        $entityRepository->expects(static::once())
            ->method('find')
            ->with(1)
            ->willReturn($entity);


        $this->workflowManagerMock->expects(static::once())
            ->method('hasApplicableWorkflows')
            ->with($entity)
            ->willReturn(true);

        $workflowMock = $this->createMock(Workflow::class);
        $workflowMock->expects(static::once())
            ->method('getName')
            ->willReturn(self::WORKFLOW);

        $this->workflowManagerMock->expects(static::once())
            ->method('getApplicableWorkflows')
            ->with($entity)
            ->willReturn([self::WORKFLOW => $workflowMock]);

        $this->workflowManagerMock->expects(static::once())
            ->method('startWorkflow')
            ->with(OrderWorkflowStartListener::WORKFLOW, $entity, OrderWorkflowStartListener::TRANSIT_TO_STEP);

        $this->orderWorkflowStartListener->postFlush($eventPostFlushArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function testTwoApplicableWorkflowsOnOrderEntity()
    {
        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $entity = $this->createMock(Order::class);
        $eventArgs->expects(static::once())
            ->method('getEntity')
            ->willReturn($entity);

        $entity->expects(static::once())
            ->method('getId')
            ->willReturn(1);

        $this->orderWorkflowStartListener->postPersist($eventArgs);
        $eventPostFlushArgs = $this->createMock(PostFlushEventArgs::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $eventPostFlushArgs->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $entityRepository = $this->createMock(EntityRepository::class);
        $entityManager->expects(static::once())
            ->method('getRepository')
            ->willReturn($entityRepository);

        $entityRepository->expects(static::once())
            ->method('find')
            ->with(1)
            ->willReturn($entity);

        $this->workflowManagerMock->expects(static::once())
            ->method('hasApplicableWorkflows')
            ->with($entity)
            ->willReturn(true);

        $workflowMock = $this->createMock(Workflow::class);

        $this->workflowManagerMock->expects(static::once())
            ->method('getApplicableWorkflows')
            ->with($entity)
            ->willReturn([self::WORKFLOW => $workflowMock, self::WORKFLOW_2 => $workflowMock]);

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
        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $entity = $this->createMock(Order::class);
        $eventArgs->expects(static::once())
            ->method('getEntity')
            ->willReturn($entity);

        $entity->expects(static::once())
            ->method('getId')
            ->willReturn(1);

        $this->orderWorkflowStartListener->postPersist($eventArgs);
        $eventPostFlushArgs = $this->createMock(PostFlushEventArgs::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $eventPostFlushArgs->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $entityRepository = $this->createMock(EntityRepository::class);
        $entityManager->expects(static::once())
            ->method('getRepository')
            ->willReturn($entityRepository);

        $entityRepository->expects(static::once())
            ->method('find')
            ->with(1)
            ->willReturn($entity);

        $this->workflowManagerMock->expects(static::once())
            ->method('hasApplicableWorkflows')
            ->with($entity)
            ->willReturn(true);

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
}
