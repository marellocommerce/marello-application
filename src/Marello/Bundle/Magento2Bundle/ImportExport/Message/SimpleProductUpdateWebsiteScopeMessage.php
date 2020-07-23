<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Message;

class SimpleProductUpdateWebsiteScopeMessage extends \ArrayObject
{
    public const INTERNAL_MAGENTO_PRODUCT_ID_KEY = 'internalMagentoProductId';
    public const PRODUCT_ID_KEY = 'productId';
    public const WEBSITE_ID_KEY = 'websiteId';
    public const PRODUCT_SKU_KEY = 'productSku';
    public const STORE_CODE_KEY = 'storeCode';
    public const PAYLOAD_KEY = 'payload';

    /**
     * @param int $internalMagentoProductId
     * @param int $productId
     * @param int $websiteId
     * @param string $productSku
     * @param string $storeCode
     * @param array $payload
     * @return SimpleProductUpdateWebsiteScopeMessage
     */
    public static function create(
        int $internalMagentoProductId,
        int $productId,
        int $websiteId,
        string $productSku,
        string $storeCode,
        array $payload
    ): SimpleProductUpdateWebsiteScopeMessage {
        $message = new static;

        $message->offsetSet(self::INTERNAL_MAGENTO_PRODUCT_ID_KEY, $internalMagentoProductId);
        $message->offsetSet(self::PRODUCT_ID_KEY, $productId);
        $message->offsetSet(self::WEBSITE_ID_KEY, $websiteId);
        $message->offsetSet(self::PRODUCT_SKU_KEY, $productSku);
        $message->offsetSet(self::STORE_CODE_KEY, $storeCode);
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
     * @return int
     */
    public function getWebsiteId(): int
    {
        return $this->offsetGet(self::WEBSITE_ID_KEY);
    }

    /**
     * @return string
     */
    public function getProductSku(): string
    {
        return $this->offsetGet(self::PRODUCT_SKU_KEY);
    }

    /**
     * @return string
     */
    public function getStoreCode(): string
    {
        return $this->offsetGet(self::STORE_CODE_KEY);
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->offsetGet(self::PAYLOAD_KEY);
    }
}
