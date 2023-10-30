<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Async;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Async\Topic\AllocateReplenishmentOrdersInventoryTopic;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine\ReplenishmentWorkflowAllocateInventoryListener;

class AllocateReplenishmentOrdersInventoryProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    const ORDERS = 'orders';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    /**
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param WorkflowManager $workflowManager
     */
    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        WorkflowManager $workflowManager
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->workflowManager = $workflowManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [AllocateReplenishmentOrdersInventoryTopic::getName()];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $data = JSON::decode($message->getBody());
        $orders = $this->entityManager
            ->getRepository(ReplenishmentOrder::class)
            ->findBy(['id' => $data[self::ORDERS]]);
        
        try {
            foreach ($orders as $order) {
                $this->transitTo(
                    $order,
                    ReplenishmentWorkflowAllocateInventoryListener::WORKFLOW,
                    ReplenishmentWorkflowAllocateInventoryListener::TRANSITION
                );
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'Unexpected exception occurred during Replenishment Orders creation',
                ['exception' => $e]
            );

            return self::REJECT;
        }

        return self::ACK;
    }

    /**
     * @param ReplenishmentOrder $order
     * @param string $workflow
     * @param string $transition
     */
    private function transitTo(ReplenishmentOrder $order, $workflow, $transition)
    {
        $workflowItem = $this->getCurrentWorkFlowItem($order, $workflow);
        if (!$workflowItem) {
            return;
        }

        $this->workflowManager->transitIfAllowed($workflowItem, $transition);
    }

    /**
     * @param ReplenishmentOrder $order
     * @param string $workflow
     * @return null|WorkflowItem
     */
    private function getCurrentWorkFlowItem(ReplenishmentOrder $order, $workflow)
    {
        $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($order);
        if (0 !== count($workflowItems)) {
            /** @var WorkflowItem $workflowItem */
            $workflowItem = array_shift($workflowItems);
            //find the follow-up workflow
            if (preg_match('/'.$workflow.'/', $workflowItem->getWorkflowName())) {
                return $workflowItem;
            }
        }
        return null;
    }
}
