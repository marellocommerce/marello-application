<?php

namespace Marello\Bundle\ProductBundle\Entity\Manager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class ProductApiEntityManager extends ApiEntityManager
{
    protected function getSerializationConfig()
    {
        $config = [
            'exclusion_policy' => 'all',
            'fields'           => [
                'id'             => null,
                'names'           => null,
                'sku'            => null,
                'status'         => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'name'  => null,
                        'label' => null,
                    ],
                ],
                'taxCode'        => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'code'  => null
                    ]
                ],
                'salesChannelTaxCodes' => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'salesChannel' => [
                            'exclusion_policy' => 'all',
                            'fields'    => [
                                'id'          => null,
                                'code'        => null,
                                'active'      => null,
                                'channelType' => null,
                            ]
                        ],
                        'taxCode'    => [
                            'exclusion_policy' => 'all',
                            'fields'           => [
                                'code'  => null
                            ]
                        ],
                    ],
                ],
                'organization'   => [
                    'exclusion_policy' => 'all',
                    'fields'           => ['id' => null],
                ],
                'prices'         => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'currency' => null,
                        'value'    => null,
                    ],
                ],
                'channelPrices'         => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'currency' => null,
                        'value'    => null,
                        'channel'  => [
                            'exclusion_policy' => 'all',
                            'fields'           => [
                                'id' => null,
                                'code' => null
                            ],
                        ],
                    ],
                ],
                'channels'       => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'id'          => null,
                        'name'        => null,
                        'code'        => null,
                        'active'      => null,
                        'channelType' => null,
                    ],
                ],
                'inventoryItem' => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'id'          => null,
                        'inventoryLevels'  => [
                            'exclusion_policy' => 'all',
                            'fields' => [
                                'inventory' => null,
                                'allocatedInventory' => null,
                                'warehouse' => [
                                    'exclusion_policy' => 'all',
                                    'fields'           => ['id' => null],
                                ],
                            ]
                        ],
                        
                    ],
                ],
                'createdAt'      => null,
                'updatedAt'      => null,
            ],
        ];

        return $config;
    }
}
