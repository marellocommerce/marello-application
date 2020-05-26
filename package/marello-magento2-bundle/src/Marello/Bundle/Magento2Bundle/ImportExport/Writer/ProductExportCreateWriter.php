<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Writer;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\Magento2Bundle\Entity\Product as InternalMagentoProduct;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class ProductExportCreateWriter extends AbstractExportWriter
{
    /**
     * @param $item
     * @return mixed|void
     */
    protected function doWrite($item)
    {
        $responseData = $this->getTransport()->createProduct($item['payload']);

        /** @var EntityManager $productEm */
        $productEm = $this->registry->getManagerForClass(Product::class);
        /** @var Product $product */
        $product = $productEm->getReference(Product::class, $item['productId']);

        /** @var EntityManager $em */
        $internalProductEm = $this->registry->getManagerForClass(InternalMagentoProduct::class);
        $className = ExtendHelper::buildEnumValueClassName(InternalMagentoProduct::STATUS_CODE);
        $readyStatus = $internalProductEm
            ->getRepository($className)
            ->find(ExtendHelper::buildEnumValueId(InternalMagentoProduct::STATUS_READY));

        /** @var InternalMagentoProduct $internalMagentoProduct */
        $internalMagentoProduct = new InternalMagentoProduct();
        $internalMagentoProduct->setOriginId($responseData['id']);
        $internalMagentoProduct->setChannel($this->getChannel());
        $internalMagentoProduct->setProduct($product);
        $internalMagentoProduct->setStatus($readyStatus);

        $internalProductEm->persist($internalMagentoProduct);
        $internalProductEm->flush($internalMagentoProduct);
    }
}
