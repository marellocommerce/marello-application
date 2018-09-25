<?php

namespace Marello\Bundle\OrderBundle\Entity\Manager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

use Marello\Bundle\ExtendWorkflowBundle\Model\WorkflowItemApiAwareInterface;

class OrderApiEntityManager extends ApiEntityManager implements WorkflowItemApiAwareInterface
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
                'phone'        => [],
            ],
        ];

        $itemConfig = [
            'exclusion_policy'      => 'all',
            'fields'                => [
                'id'                => [],
                'productName'       => [],
                'productSku'        => [],
                'quantity'          => [],
                'price'             => [],
                'originalPriceExclTax'     => [],
                'originalPriceInclTax'     => [],
                'purchasePriceIncl' => [],
                'tax'               => [],
                'taxPercent'        => [],
                'rowTotalExclTax'          => [],
                'rowTotalInclTax'          => [],
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
                'shippingAmountInclTax'  => [],
                'shippingAmountExclTax'  => [],
                'salesChannel'    => [
                    'exclusion_policy' => 'all',
                    'fields'           => [
                        'code' => [],
                    ],
                ],
                'workflowItems'   => $this->getWorkflowSerializationConfig(),
                'items'           => $itemConfig,
                'billingAddress'  => $addressConfig,
                'shippingAddress' => $addressConfig,
            ],
        ];

        return $config;
    }

    /**
     * Get Serialization config for Workflow Item
     * @return array
     */
    public function getWorkflowSerializationConfig()
    {
        $config = [
            'exclusion_policy' => 'all',
            'fields'           => [
                'id' => [],
                'entityId' => [],
                'currentStep'    => [
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
