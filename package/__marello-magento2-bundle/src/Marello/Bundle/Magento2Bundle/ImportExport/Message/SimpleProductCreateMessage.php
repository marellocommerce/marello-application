<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Message;

class SimpleProductCreateMessage extends \ArrayObject
{
    public const PRODUCT_ID_KEY = 'productId';
    public const PAYLOAD_KEY = 'payload';
    public const WEBSITE_IDS_KEY = 'websiteIds';

    /**
     * @param int $productId
     * @param array $payload
     * @param array $websiteIds
     * @return SimpleProductCreateMessage
     */
    public static function create(
        int $productId,
        array $payload,
        array $websiteIds
    ): SimpleProductCreateMessage {
        $message = new static;

        $message->offsetSet(self::PRODUCT_ID_KEY, $productId);
        $message->offsetSet(self::PAYLOAD_KEY, $payload);
        $message->offsetSet(self::WEBSITE_IDS_KEY, $websiteIds);

        return $message;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->offsetGet(self::PRODUCT_ID_KEY);
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->offsetGet(self::PAYLOAD_KEY);
    }

    /**
     * @return array
     */
    public function getWebsiteIds(): array
    {
        return $this->offsetGet(self::WEBSITE_IDS_KEY);
    }
}
