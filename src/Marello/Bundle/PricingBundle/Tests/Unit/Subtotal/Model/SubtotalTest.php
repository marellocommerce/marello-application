<?php

namespace Marello\Bundle\PricingBundle\Tests\Unit\Subtotal\Model;

use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;

class SubtotalTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $type = 'type';
        $label ='label';
        $amount = 'amount';
        $currency = 'currency';
        $operation = 'operation';
        $visible = 'visible';
        $sortOrder = 'sortOrder';

        $parameterBag = new Subtotal(
            [
                Subtotal::TYPE_FIELD => $type,
                Subtotal::LABEL_FIELD => $label,
                Subtotal::AMOUNT_FIELD => $amount,
                Subtotal::CURRENCY_FIELD => $currency,
                Subtotal::OPERATION_FIELD => $operation,
                Subtotal::VISIBLE_FIELD => $visible,
                Subtotal::SORT_ORDER_FIELD => $sortOrder,
            ]
        );

        static::assertEquals($type, $parameterBag->getType());
        static::assertEquals($label, $parameterBag->getLabel());
        static::assertEquals($amount, $parameterBag->getAmount());
        static::assertEquals($currency, $parameterBag->getCurrency());
        static::assertEquals($operation, $parameterBag->getOperation());
        static::assertEquals($visible, $parameterBag->isVisible());
        static::assertEquals($sortOrder, $parameterBag->getSortOrder());
    }
}
