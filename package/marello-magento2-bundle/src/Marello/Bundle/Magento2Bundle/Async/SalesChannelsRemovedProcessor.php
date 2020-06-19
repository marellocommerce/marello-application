<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Doctrine\DBAL\Exception\RetryableException;
use Marello\Bundle\Magento2Bundle\Scheduler\ProductSchedulerInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class SalesChannelsRemovedProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
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
            $wrappedMessage = SalesChannelsRemovedMessage::createFromMessage($message);
            $context = $wrappedMessage->getContextParams();

            if (!$this->isIntegrationApplicable($wrappedMessage)) {
                $this->logger->info(
                    '[Magento 2] Integration is not available or disabled. ' .
                    'Can\'t process removing of sales channel.',
                    $context
                );

                return self::REJECT;
            }

            $jobName = sprintf(
                '%s:%s',
                'marello_magento2:sales_channels_removed',
                $wrappedMessage->getIntegrationId()
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
                '[Magento 2] Processing removing sales channel failed. Reason: ' . $exception->getMessage(),
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
     * @param SalesChannelsRemovedMessage $message
     */
    protected function processChangedProducts(SalesChannelsRemovedMessage $message): void
    {
        $this->productScheduler->scheduleDeleteProductsOnChannel(
            $message->getIntegrationId(),
            $message->getRemovedProductIds()
        );

        $this->productScheduler->scheduleUpdateProductsOnChannel(
            $message->getIntegrationId(),
            $message->getRemovedProductIds()
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::SALES_CHANNELS_REMOVED];
    }

    /**
     * @param IntegrationAwareMessageInterface $message
     * @return bool
     */
    protected function isIntegrationApplicable(IntegrationAwareMessageInterface $message): bool
    {
        /** @var Integration $integration */
        $integration = $this->managerRegistry
            ->getRepository(Integration::class)
            ->find($message->getIntegrationId());

        return $integration && $integration->isEnabled();
    }
}
