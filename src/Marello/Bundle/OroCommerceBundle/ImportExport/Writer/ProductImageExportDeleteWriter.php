<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportCreateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;
use Marello\Bundle\ProductBundle\Entity\Product;

class ProductImageExportDeleteWriter extends AbstractExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->deleteProductImage($data[ProductExportUpdateReader::ID_FILTER]);
        if ($response->getStatusCode() === 204) {
            $em = $this->registry->getManagerForClass(Product::class);
            /** @var Product $processedProduct */
            $processedProduct = $em
                ->getRepository(Product::class)
                ->findOneBy(['sku' => $data[ProductExportCreateReader::SKU_FILTER]]);

            if ($processedProduct) {
                $productData = $processedProduct->getData();
                if (isset($productData[ProductImageExportCreateWriter::IMAGE_ID_FIELD]) &&
                    isset($productData[ProductImageExportCreateWriter::IMAGE_ID_FIELD][$this->channel->getId()])) {
                    unset($productData[ProductImageExportCreateWriter::IMAGE_ID_FIELD][$this->channel->getId()]);
                }
                $processedProduct->setData($productData);

                $em->persist($processedProduct);
                $em->flush();
            }
            $this->context->incrementDeleteCount();
        }
    }
}
