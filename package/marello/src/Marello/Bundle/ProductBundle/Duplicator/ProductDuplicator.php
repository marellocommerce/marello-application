<?php

namespace Marello\Bundle\ProductBundle\Duplicator;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Marello\Bundle\ProductBundle\Event\ProductDuplicateAfterEvent;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\FileManager;
use Oro\Bundle\AttachmentBundle\Provider\AttachmentProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductDuplicator
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    
    /**
     * @var SkuIncrementorInterface
     */
    protected $skuIncrementor;

    /**
     * @var FileManager
     */
    protected $fileManager;

    /**
     * @var AttachmentProvider
     */
    protected $attachmentProvider;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param EventDispatcherInterface $eventDispatcher
     * @param FileManager $fileManager
     * @param AttachmentProvider $attachmentProvider
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        EventDispatcherInterface $eventDispatcher,
        FileManager $fileManager,
        AttachmentProvider $attachmentProvider
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileManager = $fileManager;
        $this->attachmentProvider = $attachmentProvider;
    }

    /**
     * @param Product $product
     * @return Product
     * @throws \Exception
     */
    public function duplicate(Product $product)
    {
        $objectManager = $this->doctrineHelper->getEntityManager($product);
        $objectManager->getConnection()->beginTransaction();

        try {
            $productCopy = $this->createProductCopy($product);

            $objectManager->persist($productCopy);
            $objectManager->flush();

            $this->eventDispatcher->dispatch(
                ProductDuplicateAfterEvent::NAME,
                new ProductDuplicateAfterEvent($productCopy, $product)
            );

            $objectManager->getConnection()->commit();
        } catch (\Exception $e) {
            $objectManager->getConnection()->rollBack();
            throw $e;
        }

        return $productCopy;
    }
    
    /**
     * @param SkuIncrementorInterface $skuIncrementor
     */
    public function setSkuIncrementor(SkuIncrementorInterface $skuIncrementor)
    {
        $this->skuIncrementor = $skuIncrementor;
    }

    /**
     * @param Product $product
     * @return Product
     */
    protected function createProductCopy(Product $product)
    {
        $productCopy = clone $product;
        $baseSku = $this->defineBaseValue($product->getSku(), 'sku');
        $newSku = $this->skuIncrementor->increment($baseSku);
        $baseName = $this->defineBaseValue($product->getName(), 'name');
        $productCopy
            ->setSku($newSku)
            ->setName(sprintf('%s-%s', $baseName, substr($newSku, strlen($baseSku) + 1)));
        $disabledStatus = $this->doctrineHelper
            ->getEntityManagerForClass(ProductStatus::class)
            ->getRepository(ProductStatus::class)
            ->find(ProductStatus::DISABLED);
        $productCopy->setStatus($disabledStatus);

        $this->cloneChildObjects($product, $productCopy);

        return $productCopy;
    }

    /**
     * @param Product $product
     * @param Product $productCopy
     */
    protected function cloneChildObjects(Product $product, Product $productCopy)
    {
        foreach ($product->getChannels() as $channel) {
            $productCopy->addChannel($channel);
        }

        foreach ($product->getPrices() as $price) {
            $productCopy->addPrice(clone $price);
        }

        foreach ($product->getChannelPrices() as $channelPrice) {
            $productCopy->addChannelPrice(clone $channelPrice);
        }

        foreach ($product->getSalesChannelTaxCodes() as $salesChannelTaxCode) {
            $productCopy->addSalesChannelTaxCode(clone $salesChannelTaxCode);
        }

        foreach ($product->getSuppliers() as $supplier) {
            $productCopy->addSupplier(clone $supplier);
        }

        if ($productImage = $product->getImage()) {
            /** @var File $imageFileCopy */
            $imageFileCopy = $this->fileManager->cloneFileEntity($productImage);

            $this->doctrineHelper->getEntityManager($imageFileCopy)->persist($imageFileCopy);
            $productCopy->setImage($imageFileCopy);
        }

        $attachments = $this->attachmentProvider->getEntityAttachments($product);

        foreach ($attachments as $attachment) {
            $attachmentCopy = clone $attachment;
            $attachmentFileCopy = $this->fileManager->cloneFileEntity($attachment->getFile());
            $attachmentCopy->setFile($attachmentFileCopy);

            $attachmentCopy->setTarget($productCopy);

            $this->doctrineHelper->getEntityManager($attachmentCopy)->persist($attachmentCopy);
        }
    }

    /**
     * @param string $value
     * @param string $field
     * @return string
     */
    protected function defineBaseValue($value, $field)
    {
        if (preg_match('/^(.*)-Copy\d+$/', $value, $matches)) {
            $baseValue = $matches[1];
            $repository = $this->doctrineHelper
                ->getEntityManagerForClass(Product::class)
                ->getRepository(Product::class);
            if ($repository->findOneBy([$field => $baseValue])) {
                return $baseValue;
            }
        }

        return $value;
    }
}
