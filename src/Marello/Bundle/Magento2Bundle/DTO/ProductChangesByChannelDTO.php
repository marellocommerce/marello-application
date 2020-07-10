<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\ProductBundle\Entity\Product;

class ProductChangesByChannelDTO
{
    /** @var int */
    protected $integrationChannelId;

    /** @var array */
    protected $removedProductIdsWithSku = [];

    /** @var array */
    protected $updatedProductIds = [];

    /** @var Product[] */
    protected $insertedProducts = [];

    /** @var array */
    protected $assignedFromChannelProductIds = [];

    /** @var array */
    protected $unassignedFromChannelProductIds = [];

    /** @var ProductChangesByWebsiteDTO[] */
    protected $productChangesByWebsiteDTOs = [];

    /**
     * @param int $integrationChannelId
     */
    public function __construct(int $integrationChannelId)
    {
        $this->integrationChannelId = $integrationChannelId;
    }

    /**
     * @param Product $product
     */
    public function addRemovedProduct(Product $product): void
    {
        if (null === $product->getId()) {
            return;
        }

        $this->removedProductIdsWithSku[$product->getId()] = $product->getSku();
    }

    /**
     * @param Product $product
     */
    public function addInsertedProduct(Product $product)
    {
        $this->insertedProducts[spl_object_id($product)] = $product;
    }

    /**
     * @param Product $product
     */
    public function addUpdatedProduct(Product $product)
    {
        if (null === $product->getId()) {
            return;
        }

        $this->updatedProductIds[$product->getId()] = $product->getId();
    }

    /**
     * @param Product $product
     * @param int $websiteId
     */
    public function addProductChangesForWebsiteScope(Product $product, int $websiteId)
    {
        $productChangesByWebsite = $this->createOrGetProductChangesForWebsiteScope($websiteId);
        $productChangesByWebsite->addUpdatedProduct($product);
    }

    /**
     * @return array|ProductChangesByWebsiteDTO[]
     */
    public function getProductChangesForWebsiteScopeArray(): array
    {
        return $this->productChangesByWebsiteDTOs;
    }

    /**
     * @param Product $product
     */
    public function addUnassignedProduct(Product $product)
    {
        if (null === $product->getId()) {
            return;
        }

        $this->unassignedFromChannelProductIds[$product->getId()] = $product->getId();
    }

    /**
     * @param Product $product
     */
    public function addAssignedProduct(Product $product)
    {
        if (null === $product->getId()) {
            return;
        }

        $this->assignedFromChannelProductIds[$product->getId()] = $product->getId();
    }

    /**
     * @return array
     */
    public function getRemovedProductIds(): array
    {
        return \array_keys($this->removedProductIdsWithSku);
    }

    /**
     * @return array
     */
    public function getRemovedProductSKUs(): array
    {
        return $this->removedProductIdsWithSku;
    }

    /**
     * @return array
     */
    public function getInsertedProductIds(): array
    {
        return \array_unique(
            \array_map(function (Product $product) {
                return $product->getId();
            }, $this->insertedProducts)
        );
    }

    /**
     * Checks that method called on postFlush when product filled with their new id
     *
     * @return array
     * @throws RuntimeException
     */
    public function getInsertedProductIdsWithCountChecking(): array
    {
        $productIdArray = $this->getInsertedProductIds();
        if (count($productIdArray) !== count($this->insertedProducts)) {
            throw new RuntimeException('Some of products has no identifiers !');
        }

        return $productIdArray;
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
    public function getUnassignedProductIds(): array
    {
        return $this->unassignedFromChannelProductIds;
    }

    /**
     * @return array
     */
    public function getAssignedProductIds(): array
    {
        return $this->assignedFromChannelProductIds;
    }

    /**
     * @param int $websiteId
     * @return ProductChangesByWebsiteDTO
     */
    protected function createOrGetProductChangesForWebsiteScope(int $websiteId): ProductChangesByWebsiteDTO
    {
        if (!\array_key_exists($websiteId, $this->productChangesByWebsiteDTOs)) {
            $this->productChangesByWebsiteDTOs[$websiteId] = new ProductChangesByWebsiteDTO(
                $this->integrationChannelId,
                $websiteId
            );
        }

        return $this->productChangesByWebsiteDTOs[$websiteId];
    }
}
