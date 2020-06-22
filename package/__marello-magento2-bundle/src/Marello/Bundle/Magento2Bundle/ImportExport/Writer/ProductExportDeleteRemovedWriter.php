<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Writer;

class ProductExportDeleteRemovedWriter extends AbstractExportWriter
{
    /**
     * @param string $sku
     */
    protected function doWrite($sku): void
    {
        if (!$sku) {
            $this->logger->error("[Magento 2] Can't delete product by empty sku.");

            return;
        }

        $this->logger->info(
            sprintf('[Magento 2] Starting removing product with SKU "%s".', $sku)
        );

        $this->getTransport()->removeProduct($sku);

        $this->logger->info(
            sprintf('[Magento 2] Product with SKU "%s" successfully removed.', $sku)
        );
    }
}
