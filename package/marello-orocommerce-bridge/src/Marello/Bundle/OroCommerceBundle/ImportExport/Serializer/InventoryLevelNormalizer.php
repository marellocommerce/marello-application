<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;
use Marello\Bundle\ProductBundle\Entity\Product;

class InventoryLevelNormalizer extends AbstractNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($object instanceof VirtualInventoryLevel && isset($context['channel'])) {
            /** @var Product $product */
            $product = $object->getProduct();
            $productData = $product->getData();
            if (isset($productData[AbstractProductExportWriter::PRODUCT_ID_FIELD]) &&
                isset($productData[AbstractProductExportWriter::PRODUCT_ID_FIELD][$context['channel']])) {
                $data = [
                    'data' => [
                        'type' => 'inventorylevels',
                        'attributes' => [
                            'quantity' => $object->getInventoryQty(),
                        ],
                        'relationships' => [
                            'product' => [
                                'data' => [
                                    'type' => 'products',
                                    'id' => $productData[
                                        AbstractProductExportWriter::PRODUCT_ID_FIELD][$context['channel']
                                    ]
                                ]
                            ],
                            'productUnitPrecision' => [
                                'data' => [
                                    'type' => 'productunitprecisions',
                                    'id' => $productData[
                                    AbstractProductExportWriter::UNIT_PRECISION_ID_FIELD][$context['channel']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                if (isset($productData[AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD]) &&
                    isset($productData[AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD][$context['channel']])
                ) {
                    $data['data']['id'] =
                        $productData[AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD][$context['channel']];
                }

                return $data;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return ($data instanceof VirtualInventoryLevel && isset($context['channel']) &&
            $this->getIntegrationChannel($context['channel']));
    }
}
