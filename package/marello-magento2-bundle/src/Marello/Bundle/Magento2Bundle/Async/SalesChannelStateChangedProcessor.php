<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Doctrine\DBAL\Exception\RetryableException;
use Marello\Bundle\Magento2Bundle\Batch\Step\ExclusiveItemStep;
use Marello\Bundle\Magento2Bundle\Integration\Connector\ProductConnector;
use Marello\Bundle\Magento2Bundle\Provider\SalesChannelInfosProvider;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Component\DependencyInjection\ServiceLink;
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

    /** @var JobRunner */
    protected $jobRunner;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var SalesChannelInfosProvider */
    protected $salesChannelInfosProvider;

    /** @var ServiceLink */
    protected $syncScheduler;

    /**
     * @param JobRunner $jobRunner
     * @param ManagerRegistry $managerRegistry
     * @param SalesChannelInfosProvider $salesChannelInfosProvider
     * @param ServiceLink $syncScheduler
     */
    public function __construct(
        JobRunner $jobRunner,
        ManagerRegistry $managerRegistry,
        SalesChannelInfosProvider $salesChannelInfosProvider,
        ServiceLink $syncScheduler
    ) {
        $this->jobRunner = $jobRunner;
        $this->managerRegistry = $managerRegistry;
        $this->salesChannelInfosProvider = $salesChannelInfosProvider;
        $this->syncScheduler = $syncScheduler;
    }

    /**
     * {@inheritDoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $context = [];
        try {
            $salesChannelStateChangedMessage = SalesChannelStateChangedMessage::createFromMessage($message);
            $context = $salesChannelStateChangedMessage->getContextParams();

            $salesChannel = $this->getSalesChannel($salesChannelStateChangedMessage);
            if (null === $salesChannel) {
                return self::REJECT;
            }

            $jobName = sprintf(
                '%s:%s',
                'marello.magento2.sales_channel_state_changed',
                $salesChannel->getId()
            );

            $result = $this->jobRunner->runUnique(
                $message->getMessageId(),
                $jobName,
                function (JobRunner $jobRunner) use ($salesChannel) {
                    $integrationId = $this->salesChannelInfosProvider->getIntegrationIdBySalesChannelId(
                        $salesChannel->getId(),
                        false
                    );

                    $productIds = $this->getProductIdsBySalesChannel($salesChannel);
                    foreach ($productIds as $index => $productId) {
                        $this->processProductId($productId, $integrationId, $salesChannel);
                    }

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
     * @param int $productId
     * @param int $integrationId
     * @param SalesChannel $salesChannel
     */
    protected function processProductId(int $productId, int $integrationId, SalesChannel $salesChannel)
    {
        /** @var Product $product */
        $product = $this->managerRegistry->getRepository(Product::class)->find($productId);
        if (null === $product) {
            return;
        }

        $salesChannels = $product->getChannels()->filter(function (SalesChannel $salesChannel) use ($integrationId) {
            $integrationIdOfCurrentSC = $this->salesChannelInfosProvider->getIntegrationIdBySalesChannelId(
                $salesChannel->getId()
            );

            return $integrationIdOfCurrentSC === $integrationId && $salesChannel->isActive();
        });

        if ($salesChannels->isEmpty()) {
            $this->syncScheduler->getService()->schedule(
                $integrationId,
                ProductConnector::TYPE,
                [
                    'ids' => [$product->getId()],
                    ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME => ProductConnector::EXPORT_STEP_DELETE_ON_CHANNEL
                ]
            );

            return;
        }

        if ($salesChannel->isActive() && 1 === $salesChannels->count()) {
            if ($salesChannels->contains($salesChannel)) {
                $this->syncScheduler->getService()->schedule(
                    $integrationId,
                    ProductConnector::TYPE,
                    [
                        'ids' => [$product->getId()],
                        ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME =>
                            ProductConnector::EXPORT_STEP_CREATE
                    ]
                );
            } else {
                /**
                 * Case when Sales Channel was disabled and after that product was un-assigned from Sales Channel
                 */
                $this->syncScheduler->getService()->schedule(
                    $integrationId,
                    ProductConnector::TYPE,
                    [
                        'ids' => [$product->getId()],
                        ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME =>
                            ProductConnector::EXPORT_STEP_UPDATE
                    ]
                );
            }

            return;
        }

        $this->syncScheduler->getService()->schedule(
            $integrationId,
            ProductConnector::TYPE,
            [
                'ids' => [$product->getId()],
                ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME =>
                    ProductConnector::EXPORT_STEP_UPDATE
            ]
        );
    }

    /**
     * @param SalesChannel $salesChannel
     * @return array
     */
    protected function getProductIdsBySalesChannel(SalesChannel $salesChannel): array
    {
        /** @var SalesChannel $salesChannel */
        $productIds = $this->managerRegistry
            ->getRepository(Product::class)
            ->getProductIdsBySalesChannelIds([$salesChannel->getId()]);

        return $productIds;
    }

    /**
     * @param SalesChannelStateChangedMessage $salesChannelStateChangedMessage
     * @return SalesChannel|null
     */
    protected function getSalesChannel(SalesChannelStateChangedMessage $salesChannelStateChangedMessage): ?SalesChannel
    {
        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->managerRegistry
            ->getRepository(SalesChannel::class)
            ->find($salesChannelStateChangedMessage->getSalesChannelId());

        if (null === $salesChannel) {
            $this->logger->critical(
                '[Magento 2] Couldn\'t find Sales Channel to process changing of Sales Channel state. '.
                'The message will skip.',
                $salesChannelStateChangedMessage->getContextParams()
            );

            return null;
        }

        if ($salesChannel->isActive() !== $salesChannelStateChangedMessage->isActive()) {
            $this->logger->warning(
                '[Magento 2] Sales Channel has changed its state on opposite after message was sent. ' .
                'The message will skip.',
                $salesChannelStateChangedMessage->getContextParams()
            );

            return null;
        }

        $integrationId = $this->salesChannelInfosProvider->getIntegrationIdBySalesChannelId(
            $salesChannel->getId(),
            $salesChannel->isActive()
        );
        if (null !== $integrationId) {
            $this->logger->critical(
                '[Magento 2] Sales Channel doesn\'t link to any integration. The message will skip.',
                $salesChannelStateChangedMessage->getContextParams()
            );

            return null;
        }

        return $salesChannel;
    }
}
