<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Writer;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\Magento2Bundle\Entity\Product as InternalMagentoProduct;
use Marello\Bundle\Magento2Bundle\ImportExport\Message\ProductDeleteOnChannelMessage;

class ProductExportDeleteOnChannelWriter extends AbstractExportWriter
{
    /**
     * @param ProductDeleteOnChannelMessage $item
     */
    protected function doWrite($item): void
    {
        if (!$item instanceof ProductDeleteOnChannelMessage) {
            $this->logger->warning(
                '[Magento 2] Given incorrect input data for writing in ProductExportDeleteOnChannelWriter.',
                [
                    'expected' => ProductDeleteOnChannelMessage::class,
                    'given' => is_object($item) ? get_class($item) : gettype($item)
                ]
            );

            return;
        }

        $this->logger->info(
            sprintf('[Magento 2] Starting removing product with SKU "%s".', $item->getProductSku())
        );

        if ($item->getProductOriginWebsiteIds() || !empty($item->getProductOriginWebsiteIds())) {
            foreach ($item->getProductOriginWebsiteIds() as $originWebsiteId) {
                 $this->getTransport()->removeProductFromWebsite($item->getProductSku(), $originWebsiteId);
            }
        } else {
            $this->getTransport()->removeProduct($item->getProductSku());
        }

        $this->logger->info(
            sprintf('[Magento 2] Product with SKU "%s" was successfully removed.', $item->getProductSku())
        );

        /** @var EntityManager $em */
        $internalProductEm = $this->registry->getManagerForClass(InternalMagentoProduct::class);
        $internalMagentoProduct = $internalProductEm->find(
            InternalMagentoProduct::class,
            $item->getInternalMagentoProductId()
        );

        $internalProductEm->remove($internalMagentoProduct);
        $internalProductEm->flush($internalMagentoProduct);
    }
}
