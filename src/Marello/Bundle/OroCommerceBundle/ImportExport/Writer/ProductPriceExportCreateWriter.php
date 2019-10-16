<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\ProductBundle\Entity\Product;

class ProductPriceExportCreateWriter extends AbstractProductExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->createProductPrice($data);

        if (isset($response['data']) && isset($response['data']['type']) && isset($response['data']['id']) &&
            $response['data']['type'] === 'productprices') {
            $em = $this->registry->getManagerForClass(Product::class);
            $sku = $response['included'][0]['attributes']['sku'];
            /** @var Product $processedProduct */
            $processedProduct = $em
                ->getRepository(Product::class)
                ->findOneBy(['sku' => $sku]);

            if ($processedProduct) {
                $productData = $processedProduct->getData();
                $productData[self::PRICE_ID_FIELD][$this->channel->getId()] = $response['data']['id'];
                $processedProduct->setData($productData);

                $em->persist($processedProduct);
                $em->flush();
            }
            $this->context->incrementAddCount();
        }
    }
}
