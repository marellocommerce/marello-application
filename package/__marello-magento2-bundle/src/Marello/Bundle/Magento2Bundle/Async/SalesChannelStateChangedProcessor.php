<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Doctrine\DBAL\Exception\RetryableException;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\Magento2Bundle\Scheduler\ProductSchedulerInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class SalesChannelStateChangedProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;
    use LoggerAwareTrait;

    /** @var JobRunner */
    protected $jobRunner;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var ProductSchedulerInterface */
    protected $productScheduler;

    /**
     * @param JobRunner $jobRunner
     * @param ManagerRegistry $managerRegistry
     * @param ProductSchedulerInterface $productScheduler
     */
    public function __construct(
        JobRunner $jobRunner,
        ManagerRegistry $managerRegistry,
        ProductSchedulerInterface $productScheduler
    ) {
        $this->jobRunner = $jobRunner;
        $this->managerRegistry = $managerRegistry;
        $this->productScheduler = $productScheduler;
    }

    /**
     * {@inheritDoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $context = [];
        try {
            $wrappedMessage = SalesChannelStateChangedMessage::createFromMessage($message);
            $context = $wrappedMessage->getContextParams();

            if (!$this->isIntegrationApplicable($wrappedMessage)) {
                $this->logger->info(
                    '[Magento 2] Integration is not available or disabled. ' .
                    'Reject to process changing of Sales Channel state.',
                    $context
                );

                return self::REJECT;
            }

            $jobName = sprintf(
                '%s:%s',
                'marello_magento2:sales_channel_state_changed',
                $wrappedMessage->getSalesChannelId()
            );

            $result = $this->jobRunner->runUnique(
                $message->getMessageId(),
                $jobName,
                function (JobRunner $jobRunner) use ($wrappedMessage) {
                    $this->processChangedProducts($wrappedMessage);

                    return true;
                }
            );
        } catch (\Throwable $exception) {
            $context['exception'] = $exception;

            $this->logger->critical(
                '[Magento 2] Sales Channel state synchronization failed. Reason: ' . $exception->getMessage(),
                $context
            );

            if ($exception instanceof RetryableException) {
                return self::REQUEUE;
            }

            return self::REJECT;
        }

        /**
         * Requeue in case when same unique job already running
         */
        return $result ? self::ACK : self::REQUEUE;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::SALES_CHANNEL_STATE_CHANGED];
    }

    /**
     * @param SalesChannelStateChangedMessage $message
     */
    protected function processChangedProducts(SalesChannelStateChangedMessage $message): void
    {
        $this->productScheduler->scheduleDeleteProductsOnChannel(
            $message->getIntegrationId(),
            $message->getRemovedProductIds()
        );

        $salesChannel = $this->getSalesChannel($message);
        if (null === $salesChannel) {
            return;
        }

        $website = $salesChannel->getMagento2Websites()->first();

        $this->productScheduler->scheduleCreateProductsOnChannel(
            $message->getIntegrationId(),
            $message->getCreatedProductIds()
        );

        foreach ($message->getUpdatedProductIds() as $productId) {
            $this->productScheduler->scheduleUpdateProductOnChannel(
                $message->getIntegrationId(),
                $productId
            );

            /**
             * We should put website scope data for product in case if website is activated\re-activated,
             * because when product removes from website all product website scope data removes alongside to it.
             */
            if ($website instanceof Website && $message->isActive()) {
                $this->productScheduler->scheduleUpdateWebsiteScopeDataProductOnChannel(
                    $message->getIntegrationId(),
                    $website->getId(),
                    $productId
                );
            }
        }
    }

    /**
     * @param SalesChannelStateChangedMessage $message
     * @return bool
     */
    protected function isIntegrationApplicable(SalesChannelStateChangedMessage $message): bool
    {
        /** @var Integration $integration */
        $integration = $this->managerRegistry
            ->getRepository(Integration::class)
            ->find($message->getIntegrationId());

        return $integration && $integration->isEnabled();
    }

    /**
     * @param SalesChannelStateChangedMessage $message
     * @return SalesChannel|null
     */
    protected function getSalesChannel(SalesChannelStateChangedMessage $message): ?SalesChannel
    {
        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->managerRegistry
            ->getRepository(SalesChannel::class)
            ->find($message->getSalesChannelId());

        return $salesChannel;
    }
}
