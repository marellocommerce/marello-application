<?php

namespace Marello\Bundle\ReturnBundle\Entity\Manager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

use Marello\Bundle\ExtendWorkflowBundle\Model\WorkflowItemApiAwareInterface;

class ReturnApiEntityManager extends ApiEntityManager implements WorkflowItemApiAwareInterface
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
                'workflowItems'    => $this->getWorkflowSerializationConfig()
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
