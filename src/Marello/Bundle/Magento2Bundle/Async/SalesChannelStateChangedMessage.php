<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Util\JSON;

class SalesChannelStateChangedMessage implements IntegrationAwareMessageInterface
{
    public const INTEGRATION_ID = 'integration_id';
    public const SALES_CHANNEL_ID_KEY = 'sales_channel_id';
    public const IS_ACTIVE_KEY = 'is_active';
    public const CREATED_PRODUCT_IDS = 'created_product_ids';
    public const UPDATED_PRODUCT_IDS = 'updated_product_ids';
    public const REMOVED_PRODUCT_IDS = 'removed_product_ids';

    /** @var int */
    protected $salesChannelId;

    /** @var bool */
    protected $isActive;

    /** @var int */
    protected $integrationId;

    /** @var array */
    protected $createdProductIds;

    /** @var array */
    protected $updatedProductIds;

    /** @var array */
    protected $removedProductIds;

    /**
     * @param int $integrationId
     * @param int $salesChannelId
     * @param bool $isActive
     * @param array $createdProductIds
     * @param array $updatedProductIds
     * @param array $removedProductIds
     */
    public function __construct(
        int $integrationId,
        int $salesChannelId,
        bool $isActive,
        array $createdProductIds,
        array $updatedProductIds,
        array $removedProductIds
    ) {
        $this->integrationId = $integrationId;
        $this->salesChannelId = $salesChannelId;
        $this->isActive = $isActive;
        $this->createdProductIds = $createdProductIds;
        $this->updatedProductIds = $updatedProductIds;
        $this->removedProductIds = $removedProductIds;
    }

    /**
     * @return int
     */
    public function getSalesChannelId(): int
    {
        return $this->salesChannelId;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return int
     */
    public function getIntegrationId(): int
    {
        return $this->integrationId;
    }

    /**
     * @return array
     */
    public function getCreatedProductIds(): array
    {
        return $this->createdProductIds;
    }

    /**
     * @return array
     */
    public function getUpdatedProductIds(): array
    {
        return $this->updatedProductIds;
    }

    /**
     * @return array
     */
    public function getRemovedProductIds(): array
    {
        return $this->removedProductIds;
    }

    /**
     * @return array
     */
    public function getContextParams(): array
    {
        return [
            self::INTEGRATION_ID => $this->integrationId,
            self::SALES_CHANNEL_ID_KEY => $this->salesChannelId,
            self::IS_ACTIVE_KEY => $this->isActive,
            self::CREATED_PRODUCT_IDS => $this->createdProductIds,
            self::UPDATED_PRODUCT_IDS => $this->updatedProductIds,
            self::REMOVED_PRODUCT_IDS => $this->removedProductIds
        ];
    }

    /**
     * @param MessageInterface $message
     * @return SalesChannelStateChangedMessage
     */
    public static function createFromMessage(MessageInterface $message): SalesChannelStateChangedMessage
    {
        $messageData = JSON::decode($message->getBody());

        $message = new SalesChannelStateChangedMessage(
            $messageData[self::INTEGRATION_ID],
            $messageData[self::SALES_CHANNEL_ID_KEY],
            $messageData[self::IS_ACTIVE_KEY],
            $messageData[self::CREATED_PRODUCT_IDS],
            $messageData[self::UPDATED_PRODUCT_IDS],
            $messageData[self::REMOVED_PRODUCT_IDS]
        );

        return $message;
    }
}
