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
                'id'           => [],
                'returnNumber' => [],
                'returnItems'  => [],
            ],
        ];

        return $config;
    }
}
