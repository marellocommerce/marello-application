<?php

namespace Marello\Bundle\WorkflowBundle\Async;

use Marello\Bundle\WorkflowBundle\Async\Topic\WorkflowTransitTopic;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Psr\Log\LoggerInterface;

class WorkflowTransitProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /** @var LoggerInterface $logger */
    protected $logger;

    /** @var WorkflowManager $workflowManager */
    protected $workflowManager;

    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /**
     * @param WorkflowManager $workflowManager
     * @param LoggerInterface $logger
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        WorkflowManager $workflowManager,
        LoggerInterface $logger,
        DoctrineHelper $doctrineHelper
    ) {
        $this->workflowManager = $workflowManager;
        $this->logger = $logger;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [WorkflowTransitTopic::getName()];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $data = JSON::decode($message->getBody());
        if (!isset($data['workflow_item_entity_id']) ||
            !isset($data['transition']) ||
            !isset($data['current_step_id']) ||
            !isset($data['entity_class'])
        ) {
            $this->logger->critical(
                sprintf('Got invalid message. "%s"', $message->getBody()),
                ['message' => $message]
            );

            return self::REJECT;
        }

        try {
            /** @var WorkflowItem|null $workflowItem */
            $workflowItem = $this->doctrineHelper
                ->getEntityRepositoryForClass(WorkflowItem::class)
                ->findOneBy(
                    [
                        'entityId' => $data['workflow_item_entity_id'],
                        'currentStep' => $data['current_step_id'],
                        'entityClass' => $data['entity_class']
                    ]
                );

            if (!$workflowItem) {
                $this->logger->error(
                    sprintf(
                        'Workflow is invalid. Cannot find workflowitem with entity id: "%s"',
                        $data['workflow_item_entity_id']
                    )
                );
                return self::REJECT;
            }

            $this->transitWorkflow($workflowItem, $data['transition']);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error(
                sprintf(
                    'Message is invalid: %s. Original message: "%s"',
                    $e->getMessage(),
                    $message->getBody()
                )
            );

            return self::REJECT;
        } catch (\Exception $e) {
            $this->logger->error(
                'Unexpected exception occurred during transit',
                ['exception' => $e]
            );

            return self::REJECT;
        }

        return self::ACK;
    }

    /**
     * @param WorkflowItem $workflowItem
     * @param $transition
     * @throws \Exception
     */
    private function transitWorkflow(WorkflowItem $workflowItem, $transition)
    {
        $this->workflowManager->transitIfAllowed($workflowItem, $transition);
    }
}
