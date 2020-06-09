<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Doctrine\DBAL\Exception\RetryableException;
use Marello\Bundle\Magento2Bundle\Batch\Step\ExclusiveItemStep;
use Marello\Bundle\Magento2Bundle\Integration\Connector\ProductConnector;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Component\DependencyInjection\ServiceLink;
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

    /** @var ServiceLink */
    protected $syncScheduler;

    /**
     * @param JobRunner $jobRunner
     * @param ManagerRegistry $managerRegistry
     * @param ServiceLink $syncScheduler
     */
    public function __construct(
        JobRunner $jobRunner,
        ManagerRegistry $managerRegistry,
        ServiceLink $syncScheduler
    ) {
        $this->jobRunner = $jobRunner;
        $this->managerRegistry = $managerRegistry;
        $this->syncScheduler = $syncScheduler;
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
        foreach ($message->getRemovedProductIds() as $productId) {
            $this->syncScheduler->getService()->schedule(
                $message->getIntegrationId(),
                ProductConnector::TYPE,
                [
                    'ids' => [$productId],
                    ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME =>
                        ProductConnector::EXPORT_STEP_DELETE_ON_CHANNEL
                ]
            );
        }

        foreach ($message->getUpdatedProductIds() as $productId) {
            $this->syncScheduler->getService()->schedule(
                $message->getIntegrationId(),
                ProductConnector::TYPE,
                [
                    'ids' => [$productId],
                    ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME =>
                        ProductConnector::EXPORT_STEP_UPDATE
                ]
            );
        }
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
