<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Entity;

use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRuleDestination;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRuleDestinationPostalCode;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Component\Testing\Unit\EntityTrait;

class PaymentMethodsConfigsRuleDestinationPostalCodeTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;
    use EntityTrait;

    public function testProperties()
    {
        $properties = [
            ['id', '123'],
            ['name', 'wewfe'],
            ['destination', new PaymentMethodsConfigsRuleDestination()],
        ];

        $rule = new PaymentMethodsConfigsRuleDestinationPostalCode();
        static::assertPropertyAccessors($rule, $properties);
    }
}
