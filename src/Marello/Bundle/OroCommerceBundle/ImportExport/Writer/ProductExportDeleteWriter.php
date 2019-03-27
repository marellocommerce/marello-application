<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportCreateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;
use Marello\Bundle\ProductBundle\Entity\Product;

class ProductExportDeleteWriter extends AbstractExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->deleteProduct($data[ProductExportUpdateReader::ID_FILTER]);
        if ($response->getStatusCode() === 204) {
            $em = $this->registry->getManagerForClass(Product::class);
            $sku = $data[ProductExportCreateReader::SKU_FILTER];
            $channelId = $this->channel->getId();
            /** @var Product $processedProduct */
            $processedProduct = $em
                ->getRepository(Product::class)
                ->findOneBy(['sku' => $sku]);
            if ($processedProduct) {
                $productData = $processedProduct->getData();
                $productData = $this->unsetProductData(
                    $productData,
                    AbstractProductExportWriter::PRODUCT_ID_FIELD,
                    $channelId
                );
                $productData = $this->unsetProductData(
                    $productData,
                    AbstractProductExportWriter::PRICE_ID_FIELD,
                    $channelId
                );
                $productData = $this->unsetProductData(
                    $productData,
                    AbstractProductExportWriter::UNIT_PRECISION_ID_FIELD,
                    $channelId
                );
                $productData = $this->unsetProductData(
                    $productData,
                    AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD,
                    $channelId
                );
                $productData = $this->unsetProductData(
                    $productData,
                    AbstractProductExportWriter::IMAGE_ID_FIELD,
                    $channelId
                );
                $processedProduct->setData($productData);

                $em->persist($processedProduct);
                $em->flush();
            }
            $this->context->incrementDeleteCount();
        }
    }

    /**
     * @param array $productData
     * @param string $key
     * @param int $channelId
     * @return array
     */
    private function unsetProductData($productData, $key, $channelId)
    {
        unset($productData[$key][$channelId]);
        if (empty($productData[$key])) {
            unset($productData[$key]);
        }
        
        return $productData;
    }
}
