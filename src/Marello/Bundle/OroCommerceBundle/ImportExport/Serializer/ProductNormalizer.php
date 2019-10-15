<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxCodeExportCreateWriter;
use Marello\Bundle\ProductBundle\Entity\Product;

class ProductNormalizer extends AbstractNormalizer
{
    /**
     *{@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($object instanceof Product && isset($context['channel'])) {
            $channelId = $context['channel'];
            $integrationChannel = $this->getIntegrationChannel($channelId);
            $salesChannel = $this->getSalesChannel($integrationChannel);
            /** @var OroCommerceSettings $transport */
            $transport = $integrationChannel->getTransport();
            $taxCode = null;
            if ($salesChannel) {
                $taxCode = $object->getSalesChannelTaxCode($salesChannel);
            }
            $taxCode = $taxCode ? : $object->getTaxCode();
            
            $data =  [
                'data' => [
                    'type' => 'products',
                    'attributes' => [
                        'sku' => $object->getSku(),
                        'status' => $object->getStatus()->getName(),
                        'variantFields' => [],
                        'productType' => 'simple',
                        'featured' => true,
                        'newArrival' => false
                    ],
                    'relationships' => [
                        'owner' => [
                            'data' => [
                                    'type' => 'businessunits',
                                    'id' => $transport->getBusinessUnit() ? (string)$transport->getBusinessUnit() : '1'
                                ]
                        ],
                        'names' => [
                            'data' => [
                                [
                                    'type' => 'localizedfallbackvalues',
                                    'id' => 'names-1'
                                ],
                            ]
                        ],
                        'attributeFamily' => [
                            'data' => [
                                'type' => 'attributefamilies',
                                'id' => (string)$transport->getProductFamily()
                            ]
                        ],
                        'primaryUnitPrecision' => [
                            'data' => [
                                'type' => 'productunitprecisions',
                                'id' => 'product-unit-precision-id-1'
                            ]
                        ],
                        'inventory_status' => [
                            'data' => [
                                'type' => 'prodinventorystatuses',
                                'id' => 'out_of_stock'
                            ]
                        ],
                        'manageInventory' => [
                            'data' => [
                                'type' => 'entityfieldfallbackvalues',
                                'id' => '1abcd'
                            ]
                        ],
                        'inventoryThreshold' => [
                            'data' => [
                                'type' => 'entityfieldfallbackvalues',
                                'id' => '2abcd'
                            ]
                        ],
                        'highlightLowInventory' => [
                            'data' => [
                                'type' => 'entityfieldfallbackvalues',
                                'id' => 'low1abcd'
                            ]
                        ],
                        'lowInventoryThreshold' => [
                            'data' => [
                                'type' => 'entityfieldfallbackvalues',
                                'id' => 'low2abcd'
                            ]
                        ],
                        'isUpcoming' => [
                            'data' => [
                                'type' => 'entityfieldfallbackvalues',
                                'id' => 'product-is-upcoming'
                            ]
                        ],
                        'decrementQuantity' => [
                            'data' => [
                                'type' => 'entityfieldfallbackvalues',
                                'id' => '5abcd'
                            ]
                        ],
                        'backOrder' => [
                            'data' => [
                                'type' => 'entityfieldfallbackvalues',
                                'id' => '6abcd'
                            ]
                        ],
                    ]
                ],
                'included' => [
                    [
                        'type' => 'entityfieldfallbackvalues',
                        'id' => '1abcd',
                        'attributes' => [
                            'fallback' => 'systemConfig',
                            'scalarValue' => null,
                            'arrayValue' => null
                        ]
                    ],
                    [
                        'type' => 'entityfieldfallbackvalues',
                        'id' => '2abcd',
                        'attributes' => [
                            'fallback' => null,
                            'scalarValue' => $transport->getInventoryThreshold(),
                            'arrayValue' => null
                        ]
                    ],
                    [
                        'type' => 'entityfieldfallbackvalues',
                        'id' => 'low1abcd',
                        'attributes' => [
                            'fallback' => 'systemConfig',
                            'scalarValue' => null,
                            'arrayValue' => null
                        ]
                    ],
                    [
                        'type' => 'entityfieldfallbackvalues',
                        'id' => 'low2abcd',
                        'attributes' => [
                            'fallback' => null,
                            'scalarValue' => $transport->getLowInventoryThreshold(),
                            'arrayValue' => null
                        ]
                    ],
                    [
                        'type' => 'entityfieldfallbackvalues',
                        'id' => 'product-is-upcoming',
                        'attributes' => [
                            'fallback' => null,
                            'scalarValue' => '0',
                            'arrayValue' => null
                        ]
                    ],
                    [
                        'type' => 'entityfieldfallbackvalues',
                        'id' => '5abcd',
                        'attributes' => [
                            'fallback' => null,
                            'scalarValue' => '1',
                            'arrayValue' => null
                        ]
                    ],
                    [
                        'type' => 'entityfieldfallbackvalues',
                        'id' => '6abcd',
                        'attributes' => [
                            'fallback' => null,
                            'scalarValue' => $transport->isBackOrder(),
                            'arrayValue' => null
                        ]
                    ],
                    [
                        'type' => 'localizedfallbackvalues',
                        'id' => 'names-1',
                        'attributes' => [
                            'fallback' => null,
                            'string' => $object->getName(),
                            'text' => null
                        ],
                        'relationships' => [
                            'localization' => [
                                'data' => null
                            ]
                        ]
                    ],
                    [
                        'type' => 'productunitprecisions',
                        'id' => 'product-unit-precision-id-1',
                        'attributes' => [
                            'precision' => '0',
                            'conversionRate' => '0',
                            'sell' => '1'
                        ],
                        'relationships' => [
                            'unit' => [
                                'data' => [
                                    'type' => 'productunits',
                                    'id' => $transport->getProductUnit()
                                ]
                            ]
                        ]
                    ],
                ]
            ];
            $productData = $object->getData();
            if (isset($productData[AbstractProductExportWriter::PRODUCT_ID_FIELD]) &&
                isset($productData[AbstractProductExportWriter::PRODUCT_ID_FIELD][$channelId])) {
                $data['data']['id'] =
                    $productData[AbstractProductExportWriter::PRODUCT_ID_FIELD][$channelId];
            }

            if ($taxCode) {
                $taxCodeData = $taxCode->getData();
                $productTaxCodeId = null;
                if (isset($taxCodeData[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID]) &&
                    isset($taxCodeData[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId])) {
                    $productTaxCodeId = $taxCodeData[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId];
                }
                $data['data']['relationships']['taxCode'] = [
                    'data' => [
                        'type' => 'producttaxcodes',
                        'id' => $productTaxCodeId ? : TaxCodeNormalizer::NEW_PRODUCT_TAX_CODE_ID
                    ]
                ];
                if (!$productTaxCodeId) {
                    $data['included'][] = [
                        'type' => 'producttaxcodes',
                        'id' => TaxCodeNormalizer::NEW_PRODUCT_TAX_CODE_ID,
                        'attributes' => [
                            'code' => $taxCode->getCode(),
                            'description' => $taxCode->getDescription(),
                        ],
                    ];
                }
            }

            return $data;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return ($data instanceof Product && isset($context['channel']) &&
            $this->getIntegrationChannel($context['channel']));
    }
}
