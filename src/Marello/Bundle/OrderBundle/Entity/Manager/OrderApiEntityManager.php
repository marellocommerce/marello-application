<?php

namespace Marello\Bundle\OrderBundle\Entity\Manager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class OrderApiEntityManager extends ApiEntityManager
{

    /**
     * {@inheritdoc}
     */
    protected function getSerializationConfig()
    {
        $addressConfig = [];

        $config = [
            'exclusion_policy' => 'all',
            'fields'           => [
                'id'              => [
                    'result_name' => 'id',
                ],
                'orderNumber'     => [
                    'result_name' => 'orderNumber',
                ],
                'orderReference'  => [
                    'result_name' => 'orderReference',
                ],
                'subtotal'        => [
                    'result_name' => 'subtotal',
                ],
                'totalTax'        => [
                    'result_name' => 'totalTax',
                ],
                'grandTotal'      => [
                    'result_name' => 'grandTotal',
                ],
                'items'           => [
                ],
                'billingAddress'  => $addressConfig,
                'shippingAddress' => $addressConfig,
            ],
        ];

        return $config;
    }
}
