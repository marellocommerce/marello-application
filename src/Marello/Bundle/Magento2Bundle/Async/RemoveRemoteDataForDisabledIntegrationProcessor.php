<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Doctrine\DBAL\Exception\RetryableException;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
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

class RemoveRemoteDataForDisabledIntegrationProcessor implements
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
            $wrappedMessage = RemoveRemoteDataForDisabledIntegrationMessage::createFromMessage(
                $message
            );
            $context = $wrappedMessage->getContextParams();

            if (!$this->isIntegrationApplicable($wrappedMessage)) {
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
                    $this->scheduleRemovingProducts($jobRunner, $wrappedMessage);
                    $this->unsetLinksBetweenSalesChannelsAndWebsites($wrappedMessage->getIntegrationId());

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
     * @param int $integrationId
     */
    protected function unsetLinksBetweenSalesChannelsAndWebsites(int $integrationId): void
    {
        $em = $this->managerRegistry->getManagerForClass(Website::class);

        /** @var Website[] $websites */
        $websites = $em
            ->getRepository(Website::class)
            ->findBy(['channel' => $integrationId]);

        if (empty($websites)) {
            return;
        }

        foreach ($websites as $website) {
            $website->setSalesChannel(null);
        }

        $em->flush();
    }

    /**
     * @param JobRunner $jobRunner
     * @param RemoveRemoteDataForDisabledIntegrationMessage $message
     */
    protected function scheduleRemovingProducts(
        JobRunner $jobRunner,
        RemoveRemoteDataForDisabledIntegrationMessage $message
    ): void {
        foreach ($message->getProductIdsWithSku() as $productId => $productSku) {
            $jobRunner->createDelayed(
                sprintf(
                    '%s:%s:%s',
                    'marello_magento2:remove_remote_data_for_disabled_integration',
                    $message->getIntegrationId(),
                    $productId
                ),
                function (JobRunner $jobRunner, Job $child) use ($message, $productId, $productSku) {
                    $this->producer->send(
                        Topics::REMOVE_REMOTE_PRODUCT_FOR_DISABLED_INTEGRATION,
                        [
                            RemoveRemoteProductForDisableIntegrationMessage::PRODUCT_ID => $productId,
                            RemoveRemoteProductForDisableIntegrationMessage::PRODUCT_SKU => $productSku,
                            RemoveRemoteProductForDisableIntegrationMessage::INTEGRATION_ID =>
                                $message->getIntegrationId(),
                            RemoveRemoteProductForDisableIntegrationMessage::TRANSPORT_SETTING_BAG =>
                                $message->getTransportSettingBagSerialized(),
                            RemoveRemoteProductForDisableIntegrationMessage::IS_DEACTIVATED =>
                                $message->isDeactivated(),
                            RemoveRemoteProductForDisableIntegrationMessage::IS_REMOVED => $message->isRemoved(),
                            RemoveRemoteProductForDisableIntegrationMessage::JOB_ID => $child->getId()
                        ]);
                });
        }
    }

    /**
     * @param IntegrationAwareMessageInterface $message
     * @return bool
     */
    protected function isIntegrationApplicable(IntegrationAwareMessageInterface $message): bool
    {
        if ($message->isRemoved()) {
            return true;
        }

        /** @var Integration $integration */
        $integration = $this->managerRegistry
            ->getRepository(Integration::class)
            ->find($message->getIntegrationId());

        return $integration && !$integration->isEnabled();
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::REMOVE_REMOTE_DATA_FOR_DISABLED_INTEGRATION];
    }
}
