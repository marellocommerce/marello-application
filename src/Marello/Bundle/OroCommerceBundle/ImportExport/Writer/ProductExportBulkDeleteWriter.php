<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\TaxBundle\Entity\TaxCode;

class ProductExportBulkDeleteWriter extends AbstractBulkExportWriter
{
    /**
     * {@inheritdoc}
     */
    protected function writeItems(array $entities)
    {
        $ids = [];
        /** @var Product[] $entities */
        foreach ($entities as $entity) {
            $data = $entity->getData();
            if (isset($data[ProductExportCreateWriter::PRODUCT_ID_FIELD]) &&
                isset($data[ProductExportCreateWriter::PRODUCT_ID_FIELD][$this->channel->getId()])) {
                $ids[] = $data[ProductExportCreateWriter::PRODUCT_ID_FIELD][$this->channel->getId()];
            }
        }
        $response = $this->transport->bulkDeleteProducts($ids);
        if ($response->getStatusCode() === 204) {
            $this->context->incrementDeleteCount(count($ids));
        }
    }
}
