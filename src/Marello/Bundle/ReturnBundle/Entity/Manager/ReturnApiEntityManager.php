<?php

namespace Marello\Bundle\ReturnBundle\Entity\Manager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class ReturnApiEntityManager extends ApiEntityManager
{
    /**
     * {@inheritdoc}
     */
    protected function getSerializationConfig()
    {
        $itemConfig = [
            'exclusion_policy' => 'all',
            'fields'           => [
                'productName' => [],
                'productSku'  => [],
                'quantity'    => [],
                'price'       => [],
                'tax'         => [],
                'totalPrice'  => [],
            ],
        ];

        $config = [
            'exclusion_policy' => 'all',
            'fields'           => [
                'id'           => [],
                'returnNumber' => [],
                'returnReference' => [],
                'returnItems'  => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'id'        => [],
                        'quantity'  => [],
                        'orderItem' => $itemConfig,
                        'createdAt' => [],
                        'updatedAt' => [],
                    ],
                ],
                'workflowItem'    => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'id' => [],
                        'entityId' => [],
                    ],
                ],
                'workflowStep'    => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'name' => [],
                    ],
                ],
            ],
        ];

        return $config;
    }
}
