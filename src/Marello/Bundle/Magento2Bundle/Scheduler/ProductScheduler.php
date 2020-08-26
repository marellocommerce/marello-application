<?php

namespace Marello\Bundle\Magento2Bundle\Scheduler;

use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Bundle\IntegrationBundle\Async\Topics;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

use Marello\Bundle\Magento2Bundle\Batch\Step\ActionItemStep;
use Marello\Bundle\Magento2Bundle\Integration\Connector\ProductConnector;

class ProductScheduler implements ProductSchedulerInterface
{
    /** @var $producer MessageProducerInterface */
    protected $producer;

    /**
     * @param MessageProducerInterface $producer
     */
    public function __construct(MessageProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    /**
     * {@inheritDoc}
     */
    public function scheduleRemovingProduct(int $integrationId, string $sku): void
    {
        $this->sendMessageToProducer(
            $integrationId,
            ProductConnector::TYPE,
            [
                'skus' => [$sku],
                ActionItemStep::OPTION_KEY_ACTION_NAME => ProductConnector::EXPORT_ACTION_DELETE
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function scheduleRemovingProducts(int $integrationId, array $skus): void
    {
        foreach ($skus as $sku) {
            $this->scheduleRemovingProduct($integrationId, $sku);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function scheduleDeleteProductOnChannel(int $integrationId, int $productId): void
    {
        $this->sendMessageToProducer(
            $integrationId,
            ProductConnector::TYPE,
            [
                'ids' => [$productId],
                ActionItemStep::OPTION_KEY_ACTION_NAME => ProductConnector::EXPORT_ACTION_DELETE_ON_CHANNEL
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function scheduleDeleteProductsOnChannel(int $integrationId, array $productIds): void
    {
        foreach ($productIds as $productId) {
            $this->scheduleDeleteProductOnChannel($integrationId, $productId);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function scheduleCreateProductOnChannel(int $integrationId, int $productId): void
    {
        $this->sendMessageToProducer(
            $integrationId,
            ProductConnector::TYPE,
            [
                'ids' => [$productId],
                ActionItemStep::OPTION_KEY_ACTION_NAME => ProductConnector::EXPORT_ACTION_CREATE
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function scheduleCreateProductsOnChannel(int $integrationId, array $productIds): void
    {
        foreach ($productIds as $productId) {
            $this->scheduleCreateProductOnChannel($integrationId, $productId);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function scheduleUpdateProductOnChannel(int $integrationId, int $productId): void
    {
        $this->sendMessageToProducer(
            $integrationId,
            ProductConnector::TYPE,
            [
                'ids' => [$productId],
                ActionItemStep::OPTION_KEY_ACTION_NAME => ProductConnector::EXPORT_ACTION_UPDATE
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function scheduleUpdateProductsOnChannel(int $integrationId, array $productIds): void
    {
        foreach ($productIds as $productId) {
            $this->scheduleUpdateProductOnChannel($integrationId, $productId);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function scheduleUpdateWebsiteScopeDataProductOnChannel(
        int $integrationId,
        int $websiteId,
        int $productId
    ): void {
        $this->sendMessageToProducer(
            $integrationId,
            ProductConnector::TYPE,
            [
                'ids' => [$productId],
                'website' => $websiteId,
                ActionItemStep::OPTION_KEY_ACTION_NAME => ProductConnector::EXPORT_ACTION_UPDATE_WEBSITE_SCOPE_DATA
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function scheduleUpdateWebsiteScopeDataProductsOnChannel(
        int $integrationId,
        int $websiteId,
        array $productIds
    ): void {
        foreach ($productIds as $productId) {
            $this->scheduleUpdateWebsiteScopeDataProductOnChannel($integrationId, $websiteId, $productId);
        }
    }

    /**
     * @param $integrationId
     * @param $connector
     * @param array $connectorParameters
     */
    protected function sendMessageToProducer($integrationId, $connector, array $connectorParameters = []): void
    {
        $this->producer->send(
            sprintf('%s.magento2.product', Topics::REVERS_SYNC_INTEGRATION),
            new Message(
                [
                    'integration_id'       => $integrationId,
                    'connector_parameters' => $connectorParameters,
                    'connector'            => $connector,
                    'transport_batch_size' => 100,
                ],
                MessagePriority::VERY_HIGH
            )
        );
    }
}
