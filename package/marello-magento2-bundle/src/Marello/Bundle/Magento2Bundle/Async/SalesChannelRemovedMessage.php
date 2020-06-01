<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Util\JSON;

class SalesChannelRemovedMessage extends Message
{
    public const PRODUCT_IDS_KEY = 'product_ids';
    public const INTEGRATION_ID = 'integration_id';

    /** @var array */
    protected $productIds;

    /** @var int */
    protected $integrationId;

    /**
     * @param array $productIds
     * @param int $integrationId
     * @param $body
     * @param $priority
     */
    public function __construct(array $productIds, int $integrationId, $body, $priority)
    {
        $this->productIds = $productIds;
        $this->integrationId = $integrationId;

        parent::__construct($body, $priority);
    }

    /**
     * @return array
     */
    public function getProductIds(): array
    {
        return $this->productIds;
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
    public function getContextParams(): array
    {
        return [
            self::PRODUCT_IDS_KEY => $this->productIds,
            self::INTEGRATION_ID => $this->integrationId
        ];
    }

    /**
     * @param MessageInterface $message
     * @return SalesChannelRemovedMessage
     */
    public static function createFromMessage(MessageInterface $message): SalesChannelRemovedMessage
    {
        $messageData = JSON::decode($message->getBody());

        $message = new SalesChannelRemovedMessage(
            $messageData[self::PRODUCT_IDS_KEY],
            $messageData[self::INTEGRATION_ID],
            $message->getBody(),
            $message->getPriority()
        );

        return $message;
    }
}
