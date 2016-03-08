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
                'name'           => null,
                'sku'            => null,
                'status'         => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'name'  => null,
                        'label' => null,
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
                            'fields'           => ['id' => null],
                        ],
                    ],
                ],
                'channels'       => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'id'          => null,
                        'name'        => null,
                        'active'      => null,
                        'channelType' => null,
                    ],
                ],
                'inventoryItems' => [
                    'result_name'      => 'inventory',
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'quantity'  => null,
                        'warehouse' => [
                            'exclusion_policy' => 'all',
                            'fields'           => ['id' => null],
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
