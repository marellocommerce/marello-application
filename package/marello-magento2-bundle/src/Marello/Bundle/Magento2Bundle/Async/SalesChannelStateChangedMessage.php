<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Util\JSON;

class SalesChannelStateChangedMessage extends Message
{
    public const SALES_CHANNEL_ID_KEY = 'sales_channel_id';
    public const IS_ACTIVE_KEY = 'is_active';
    public const INTEGRATION_ID = 'integration_id';

    /** @var int */
    protected $salesChannelId;

    /** @var bool */
    protected $isActive;

    /** @var int */
    protected $integrationId;

    /**
     * @param int $salesChannelId
     * @param bool $isActive
     * @param int $integrationId
     * @param $body
     * @param $priority
     */
    public function __construct(int $salesChannelId, bool $isActive, int $integrationId, $body, $priority)
    {
        $this->salesChannelId = $salesChannelId;
        $this->isActive = $isActive;
        $this->integrationId = $integrationId;

        parent::__construct($body, $priority);
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
     * @return array
     */
    public function getContextParams(): array
    {
        return [
            self::SALES_CHANNEL_ID_KEY => $this->salesChannelId,
            self::IS_ACTIVE_KEY => $this->isActive
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
            $messageData[self::SALES_CHANNEL_ID_KEY],
            $messageData[self::IS_ACTIVE_KEY],
            $message[self::INTEGRATION_ID],
            $message->getBody(),
            $message->getPriority()
        );

        return $message;
    }
}
