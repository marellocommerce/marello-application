<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\ProductBundle\Entity\Product;

class ChangesByChannelDTO
{
    /** @var int */
    protected $channelId;

    /** @var array */
    protected $removedProductIdsWithSku = [];

    /** @var array */
    protected $updatedProductIdsWithSku = [];

    /** @var Product[] */
    protected $insertedProducts = [];

    /** @var array */
    protected $assignedFromChannelProductIdsWithSku = [];

    /** @var array */
    protected $unassignedFromChannelProductIdsWithSku = [];

    /**
     * @param int $channelId
     */
    public function __construct(int $channelId)
    {
        $this->channelId = $channelId;
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

        $this->updatedProductIdsWithSku[$product->getId()] = $product->getSku();
    }

    /**
     * @param Product $product
     */
    public function addUnassignedProduct(Product $product)
    {
        if (null === $product->getId()) {
            return;
        }

        $this->unassignedFromChannelProductIdsWithSku[$product->getId()] = $product->getSku();
    }

    /**
     * @param Product $product
     */
    public function addAssignedProduct(Product $product)
    {
        if (null === $product->getId()) {
            return;
        }

        $this->assignedFromChannelProductIdsWithSku[$product->getId()] = $product->getSku();
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
            throw new RuntimeException(
                '[Magento 2] Some of products has no identifiers !'
            );
        }

        return $productIdArray;
    }

    /**
     * @return array
     */
    public function getInsertedProductSkus(): array
    {
        return \array_unique(
            \array_map(function (Product $product) {
                return $product->getSku();
            }, $this->insertedProducts)
        );
    }

    /**
     * @return array
     */
    public function getUpdatedProductIds(): array
    {
        return \array_keys($this->updatedProductIdsWithSku);
    }

    /**
     * @return array
     */
    public function getUpdatedProductSkus(): array
    {
        return $this->updatedProductIdsWithSku;
    }

    /**
     * @return array
     */
    public function getUnassignedProductIds(): array
    {
        return \array_keys($this->unassignedFromChannelProductIdsWithSku);
    }

    /**
     * @return array
     */
    public function getAssignedProductIds(): array
    {
        return \array_keys($this->assignedFromChannelProductIdsWithSku);
    }

    /**
     * @return array
     */
    public function getUnassignedProductSkus(): array
    {
        return $this->unassignedFromChannelProductIdsWithSku;
    }
}
