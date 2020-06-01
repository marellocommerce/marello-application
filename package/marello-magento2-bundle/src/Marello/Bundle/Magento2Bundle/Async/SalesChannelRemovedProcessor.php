<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Doctrine\DBAL\Exception\RetryableException;
use Marello\Bundle\Magento2Bundle\Batch\Step\ExclusiveItemStep;
use Marello\Bundle\Magento2Bundle\Integration\Connector\ProductConnector;
use Marello\Bundle\Magento2Bundle\Provider\SalesChannelInfosProvider;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\DependencyInjection\ServiceLink;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class SalesChannelRemovedProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var SalesChannelInfosProvider */
    protected $salesChannelInfosProvider;

    /** @var ServiceLink */
    protected $syncScheduler;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param SalesChannelInfosProvider $salesChannelInfosProvider
     * @param ServiceLink $syncScheduler
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        SalesChannelInfosProvider $salesChannelInfosProvider,
        ServiceLink $syncScheduler
    ) {
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
            $productAssignedWebsitesChangedMessage = SalesChannelRemovedMessage::createFromMessage($message);
            $context = $productAssignedWebsitesChangedMessage->getContextParams();

            $integration = $this->managerRegistry
                ->getRepository(Channel::class)
                ->find($productAssignedWebsitesChangedMessage->getIntegrationId());

            if (null === $integration) {
                $this->logger->critical(
                    '[Magento 2] Integration doesn\'t exist. ' .
                    "Can't process removing of sales channel.",
                    $context
                );

                return self::REJECT;
            }

            foreach ($productAssignedWebsitesChangedMessage->getProductIds() as $productId) {
                $this->processProductId($productId, $integration->getId());
            }

            return self::ACK;
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
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::SALES_CHANNEL_REMOVED];
    }

    /**
     * @param int $productId
     * @param int $integrationId
     */
    protected function processProductId(int $productId, int $integrationId)
    {
        /** @var Product $product */
        $product = $this->managerRegistry->getRepository(Product::class)->find($productId);
        if (null === $product) {
            return;
        }

        $salesChannels = $product
            ->getChannels()
            ->filter(function (SalesChannel $salesChannel) use ($integrationId) {
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
                    ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME =>
                        ProductConnector::EXPORT_STEP_DELETE_ON_CHANNEL
                ]
            );

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
}
