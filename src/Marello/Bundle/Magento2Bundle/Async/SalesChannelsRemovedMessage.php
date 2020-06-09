<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Util\JSON;

class SalesChannelsRemovedMessage extends Message implements IntegrationAwareMessageInterface
{
    public const INTEGRATION_ID = 'integration_id';
    public const UPDATED_PRODUCT_IDS_KEY = 'updated_product_ids';
    public const REMOVED_PRODUCT_IDS_KEY = 'removed_product_ids';

    /** @var int */
    protected $integrationId;

    /** @var array */
    protected $updatedProductIds;

    /** @var array */
    protected $removedProductIds;

    /**
     * @param int $integrationId
     * @param array $updatedProductIds
     * @param array $removedProductIds
     */
    public function __construct(int $integrationId, array $updatedProductIds, array $removedProductIds)
    {
        $this->integrationId = $integrationId;
        $this->updatedProductIds = $updatedProductIds;
        $this->removedProductIds = $removedProductIds;
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
            self::UPDATED_PRODUCT_IDS_KEY => $this->updatedProductIds,
            self::REMOVED_PRODUCT_IDS_KEY => $this->removedProductIds
        ];
    }

    /**
     * @param MessageInterface $message
     * @return SalesChannelsRemovedMessage
     */
    public static function createFromMessage(MessageInterface $message): SalesChannelsRemovedMessage
    {
        $messageData = JSON::decode($message->getBody());

        $message = new SalesChannelsRemovedMessage(
            $messageData[self::INTEGRATION_ID],
            $messageData[self::UPDATED_PRODUCT_IDS_KEY],
            $messageData[self::REMOVED_PRODUCT_IDS_KEY]
        );

        return $message;
    }
}
