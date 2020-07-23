<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Message;

class OrderStatusUpdateMessage extends \ArrayObject
{
    public const MAGENTO_ORDER_ORIGIN_ID_KEY = 'magentoOrderOriginId';
    public const MARELLO_ORDER_ID_KEY = 'marelloOrderId';
    public const PAYLOAD_KEY = 'payload';

    /**
     * @param int $magentoOrderOriginId
     * @param int $marelloOrderId
     * @param array $payload
     * @return OrderStatusUpdateMessage
     */
    public static function create(
        int $magentoOrderOriginId,
        int $marelloOrderId,
        array $payload
    ): OrderStatusUpdateMessage {
        $message = new static;

        $message->offsetSet(self::MAGENTO_ORDER_ORIGIN_ID_KEY, $magentoOrderOriginId);
        $message->offsetSet(self::MARELLO_ORDER_ID_KEY, $marelloOrderId);
        $message->offsetSet(self::PAYLOAD_KEY, $payload);

        return $message;
    }

    /**
     * @return int
     */
    public function getMagentoOrderOriginId(): int
    {
        return $this->offsetGet(self::MAGENTO_ORDER_ORIGIN_ID_KEY);
    }

    /**
     * @return int
     */
    public function getMarelloOrderId(): int
    {
        return $this->offsetGet(self::MARELLO_ORDER_ID_KEY);
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->offsetGet(self::PAYLOAD_KEY);
    }
}
