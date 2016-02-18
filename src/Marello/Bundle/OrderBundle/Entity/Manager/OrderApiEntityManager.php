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
        $addressConfig = [
            'exclusion_policy' => 'all',
            'fields'           => [
                'namePrefix'   => [],
                'firstName'    => [],
                'middleName'   => [],
                'lastName'     => [],
                'nameSuffix'   => [],
                'street'       => [],
                'street2'      => [],
                'city'         => [],
                'country'      => [],
                'region'       => [],
                'organization' => [],
                'postalCode'   => [],
                'email'        => [],
                'phone'        => [],
            ],
        ];

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
                'id'              => [],
                'orderNumber'     => [],
                'orderReference'  => [],
                'subtotal'        => [],
                'totalTax'        => [],
                'grandTotal'      => [],
                'paymentMethod'   => [],
                'paymentDetails'  => [],
                'shippingMethod'  => [],
                'shippingAmount'  => [],
                'items'           => $itemConfig,
                'billingAddress'  => $addressConfig,
                'shippingAddress' => $addressConfig,
            ],
        ];

        return $config;
    }
}
