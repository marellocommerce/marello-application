<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Doctrine\DBAL\Exception\RetryableException;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\Job;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class RemoveRemoteDataForDisableIntegrationProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var JobRunner */
    protected $jobRunner;

    /** @var MessageProducerInterface */
    protected $producer;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /**
     * @param JobRunner $jobRunner
     * @param MessageProducerInterface $producer
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(
        JobRunner $jobRunner,
        MessageProducerInterface $producer,
        ManagerRegistry $managerRegistry
    ) {
        $this->jobRunner = $jobRunner;
        $this->producer = $producer;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $context = [];
        try {
            $wrappedMessage = RemoveRemoteDataForDisableIntegrationMessage::createFromMessage(
                $message
            );
            $context = $wrappedMessage->getContextParams();

            if (!$this->isAllowedToStart($wrappedMessage)) {
                $this->logger->debug(
                    '[Magento 2] Remove Remote Data is not allow to start.',
                    $context
                );

                return self::REJECT;
            }

            $jobName = sprintf(
                '%s:%s',
                'marello_magento2:remove_remote_data_for_disabled_integration',
                $wrappedMessage->getIntegrationId()
            );

            $result = $this->jobRunner->runUnique(
                $message->getMessageId(),
                $jobName,
                function (JobRunner $jobRunner) use ($wrappedMessage) {
                    foreach ($wrappedMessage->getProductIdsWithSku() as $productId => $productSku) {
                        $jobRunner->createDelayed(
                            sprintf(
                                '%s:%s:%s',
                                'marello_magento2:remove_remote_data_for_disabled_integration',
                                $wrappedMessage->getIntegrationId(),
                                $productId
                            ),
                            function (JobRunner $jobRunner, Job $child) use ($wrappedMessage, $productId, $productSku) {
                                $this->producer->send(
                                    Topics::REMOVE_REMOTE_PRODUCT_FOR_DISABLED_INTEGRATION,
                                    [
                                        RemoveRemoteProductForDisableIntegrationMessage::PRODUCT_ID => $productId,
                                        RemoveRemoteProductForDisableIntegrationMessage::PRODUCT_SKU => $productSku,
                                        RemoveRemoteProductForDisableIntegrationMessage::INTEGRATION_ID =>
                                            $wrappedMessage->getIntegrationId(),
                                        RemoveRemoteProductForDisableIntegrationMessage::TRANSPORT_SETTING_BAG =>
                                            $wrappedMessage->getTransportSettingBagSerialized(),
                                        RemoveRemoteProductForDisableIntegrationMessage::IS_DEACTIVATED =>
                                            $wrappedMessage->isDeactivated(),
                                        RemoveRemoteProductForDisableIntegrationMessage::IS_REMOVED =>
                                            $wrappedMessage->isRemoved(),
                                        RemoveRemoteProductForDisableIntegrationMessage::JOB_ID => $child->getId()
                                    ]);
                            });
                    }

                    return true;
                }
            );

        } catch (\Throwable $exception) {
            $context['exception'] = $exception;

            $this->logger->critical(
                '[Magento 2] Remove Remote Data for disabled integration failed. Reason: ' .
                $exception->getMessage(),
                $context
            );

            if ($exception instanceof RetryableException) {
                return self::REQUEUE;
            }

            return self::REJECT;
        }

        /**
         * Reject in case when same unique job already running
         */
        return $result ? self::ACK : self::REJECT;
    }

    /**
     * @param RemoveRemoteDataForDisableIntegrationMessage $message
     * @return bool
     */
    protected function isAllowedToStart(RemoveRemoteDataForDisableIntegrationMessage $message): bool
    {
        if ($message->isRemoved()) {
            return true;
        }

        /** @var Channel $integration */
        $integration = $this->managerRegistry
            ->getManagerForClass(Channel::class)
            ->getRepository(Channel::class)
            ->find($message->getIntegrationId());

        if (null === $integration) {
            return true;
        }

        return !$integration->isEnabled();
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::REMOVE_REMOTE_DATA_FOR_DISABLED_INTEGRATION];
    }
}
