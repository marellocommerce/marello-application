<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Writer;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\Magento2Bundle\Entity\Product as InternalMagentoProduct;
use Marello\Bundle\ProductBundle\Entity\Product;

class ProductExportDeleteOnChannelWriter extends AbstractExportWriter
{
    /**
     * @param Product $item
     * @return mixed|void
     */
    protected function doWrite($item)
    {
        $this->logger->info(
            sprintf('[Magento 2] Starting removing product with SKU "%s".', $item->getSku())
        );

        $this->getTransport()->removeProduct($item->getSku());

        $this->logger->info(
            sprintf('[Magento 2] Product with SKU "%s" successfully removed.', $item->getSku())
        );

        $productIdsWithInternalProductIds = $this->stepExecution
                ->getJobExecution()
                ->getExecutionContext()
                ->get(InternalMagentoProductWriter::INTERNAL_MAGENTO_PRODUCT_IDS_CONTEXT) ?? [];

        /** @var InternalMagentoProduct $internalMagentoProduct */
        $internalMagentoProductId = $productIdsWithInternalProductIds[$item->getId()];

        /** @var EntityManager $em */
        $internalProductEm = $this->registry->getManagerForClass(InternalMagentoProduct::class);
        $internalMagentoProduct = $internalProductEm->getReference(
            InternalMagentoProduct::class,
            $internalMagentoProductId
        );

        $internalProductEm->remove($internalMagentoProduct);
        $internalProductEm->flush($internalMagentoProduct);
    }
}
