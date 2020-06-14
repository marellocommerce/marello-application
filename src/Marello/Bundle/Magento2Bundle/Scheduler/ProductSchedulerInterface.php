<?php

namespace Marello\Bundle\Magento2Bundle\Scheduler;

interface ProductSchedulerInterface
{
    /**
     * @param int $integrationId
     * @param string $sku
     */
    public function scheduleRemovingProduct(int $integrationId, string $sku): void;

    /**
     * @param int $integrationId
     * @param array $skus
     */
    public function scheduleRemovingProducts(int $integrationId, array $skus): void;

    /**
     * @param int $integrationId
     * @param int $productId
     */
    public function scheduleDeleteProductOnChannel(int $integrationId, int $productId): void;

    /**
     * @param int $integrationId
     * @param array $productIds
     */
    public function scheduleDeleteProductsOnChannel(int $integrationId, array $productIds): void;

    /**
     * @param int $integrationId
     * @param int $productId
     */
    public function scheduleCreateProductOnChannel(int $integrationId, int $productId): void;

    /**
     * @param int $integrationId
     * @param array $productIds
     */
    public function scheduleCreateProductsOnChannel(int $integrationId, array $productIds): void;

    /**
     * @param int $integrationId
     * @param int $productId
     */
    public function scheduleUpdateProductOnChannel(int $integrationId, int $productId): void;

    /**
     * @param int $integrationId
     * @param array $productIds
     */
    public function scheduleUpdateProductsOnChannel(int $integrationId, array $productIds): void;

    /**
     * @param int $integrationId
     * @param int $websiteId
     * @param int $productId
     */
    public function scheduleUpdateWebsiteScopeDataProductOnChannel(
        int $integrationId,
        int $websiteId,
        int $productId
    ): void;

    /**
     * @param int $integrationId
     * @param int $websiteId
     * @param array $productIds
     */
    public function scheduleUpdateWebsiteScopeDataProductsOnChannel(
        int $integrationId,
        int $websiteId,
        array $productIds
    ): void;
}
