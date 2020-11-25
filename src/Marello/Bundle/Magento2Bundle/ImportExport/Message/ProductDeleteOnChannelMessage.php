<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Message;

class ProductDeleteOnChannelMessage extends \ArrayObject
{
    public const INTERNAL_MAGENTO_PRODUCT_ID_KEY = 'internalMagentoProductId';
    public const PRODUCT_ID_KEY = 'productId';
    public const PRODUCT_SKU_KEY = 'productSku';
    public const ORIGIN_WEBSITE_IDS = 'originWebsiteIds';

    /**
     * @param int $internalMagentoProductId
     * @param int $productId
     * @param string $productSku
     * @param array $originWebsiteIds
     * @return ProductDeleteOnChannelMessage
     */
    public static function create(
        int $internalMagentoProductId,
        int $productId,
        string $productSku,
        array $originWebsiteIds = null
    ): ProductDeleteOnChannelMessage {
        $message = new static;

        $message->offsetSet(self::INTERNAL_MAGENTO_PRODUCT_ID_KEY, $internalMagentoProductId);
        $message->offsetSet(self::PRODUCT_ID_KEY, $productId);
        $message->offsetSet(self::PRODUCT_SKU_KEY, $productSku);
        $message->offsetSet(self::ORIGIN_WEBSITE_IDS, $originWebsiteIds);

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
    public function getProductOriginWebsiteIds(): ?array
    {
        return $this->offsetGet(self::ORIGIN_WEBSITE_IDS);
    }
}
