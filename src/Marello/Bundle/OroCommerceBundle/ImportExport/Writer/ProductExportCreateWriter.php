<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Product;

class ProductExportCreateWriter extends AbstractProductExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->createProduct($data);

        if (isset($response['data']) && isset($response['data']['type']) && isset($response['data']['id']) &&
            $response['data']['type'] === 'products') {
            $em = $this->registry->getManagerForClass(Product::class);
            $sku = $response['data']['attributes']['sku'];
            $channeId = $this->channel->getId();
            /** @var Product $processedProduct */
            $processedProduct = $em
                ->getRepository(Product::class)
                ->findOneBy(['sku' => $sku]);

            $this->processTaxCode($response);

            if ($processedProduct) {
                $productData = $processedProduct->getData();
                $productData[self::PRODUCT_ID_FIELD][$channeId] = $response['data']['id'];
                $productData[self::UNIT_PRECISION_ID_FIELD][$channeId] =
                    $response['data']['relationships']['primaryUnitPrecision']['data']['id'];
                $productData[self::INVENTORY_LEVEL_ID_FIELD][$channeId] =
                    $response['data']['relationships']['inventoryLevel']['data']['id'];
                $processedProduct->setData($productData);

                $em->persist($processedProduct);
                $em->flush();
            }
            $this->context->incrementAddCount();
        }
    }
}
