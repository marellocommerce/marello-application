<?php

namespace Marello\Bundle\PaymentBundle\Method;

class PaymentMethodViewFactory
{
    /**
     * @param string $paymentMethodId
     * @param string $label
     * @param int $sortOrder
     * @param array $options
     *
     * @return array
     */
    public static function createMethodView($paymentMethodId, $label, $sortOrder, $options)
    {
        return [
            'identifier' => $paymentMethodId,
            'label' => $label,
            'sortOrder' => $sortOrder,
            'options' => $options
        ];
    }
}
