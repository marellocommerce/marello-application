<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;
use Marello\Bundle\PricingBundle\Entity\BasePrice;

class ProductPriceNormalizer extends AbstractNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($object instanceof BasePrice && isset($context['channel'])) {
            $productData = $object->getProduct()->getData();
            if (isset($productData[AbstractProductExportWriter::PRODUCT_ID_FIELD]) &&
                isset($productData[AbstractProductExportWriter::PRODUCT_ID_FIELD][$context['channel']])) {
                /** @var OroCommerceSettings $transport */
                $transport = $this->getIntegrationChannel($context['channel'])->getTransport();
                $data = [
                    'data' => [
                        'type' => 'productprices',
                        'attributes' => [
                            'quantity' => 1,
                            'currency' => $object->getCurrency(),
                            'value' => $object->getValue()
                        ],
                        'relationships' => [
                            'priceList' => [
                                'data' => [
                                    'type' => 'pricelists',
                                    'id' => (string)$transport->getPriceList()
                                ]
                            ],
                            'product' => [
                                'data' => [
                                    'type' => 'products',
                                    'id' => $productData[
                                        AbstractProductExportWriter::PRODUCT_ID_FIELD][$context['channel']
                                    ]
                                ]
                            ],
                            'unit' => [
                                'data' => [
                                    'type' => 'productunits',
                                    'id' => $transport->getProductUnit()
                                ]
                            ]
                        ]
                    ]
                ];
                if (isset($productData[AbstractProductExportWriter::PRICE_ID_FIELD]) &&
                    isset($productData[AbstractProductExportWriter::PRICE_ID_FIELD][$context['channel']])
                ) {
                    $data['data']['id'] =
                        $productData[AbstractProductExportWriter::PRICE_ID_FIELD][$context['channel']];
                    unset($data['data']['relationships']['priceList']);
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
        return ($data instanceof BasePrice && isset($context['channel']) &&
            $this->getIntegrationChannel($context['channel']));
    }
}
