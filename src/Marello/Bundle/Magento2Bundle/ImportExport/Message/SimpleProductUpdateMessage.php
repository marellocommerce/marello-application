<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Message;

class SimpleProductUpdateMessage extends \ArrayObject
{
    public const INTERNAL_MAGENTO_PRODUCT_ID_KEY = 'internal_magento_product_id';
    public const PRODUCT_ID_KEY = 'productId';
    public const PRODUCT_SKU_KEY = 'productSku';
    public const PAYLOAD_KEY = 'payload';

    /**
     * @param int $internalMagentoProductId
     * @param int $productId
     * @param string $productSku
     * @param array $payload
     * @return SimpleProductUpdateMessage
     */
    public static function create(
        int $internalMagentoProductId,
        int $productId,
        string $productSku,
        array $payload
    ): SimpleProductUpdateMessage {
        $message = new static;

        $message->offsetSet(self::INTERNAL_MAGENTO_PRODUCT_ID_KEY, $internalMagentoProductId);
        $message->offsetSet(self::PRODUCT_ID_KEY, $productId);
        $message->offsetSet(self::PRODUCT_SKU_KEY, $productSku);
        $message->offsetSet(self::PAYLOAD_KEY, $payload);

        return $message;
    }

    /**
     * @return int
     */
    public function getInternalMagentoProductId(): int
    {
        return $this->offsetGet(self::INTERNAL_MAGENTO_PRODUCT_ID_KEY);
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->offsetGet(self::PRODUCT_ID_KEY);
    }

    /**
     * @return string
     */
    public function getProductSku(): string
    {
        return $this->offsetGet(self::PRODUCT_SKU_KEY);
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->offsetGet(self::PAYLOAD_KEY);
    }
}
