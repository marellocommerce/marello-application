<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Entity;

use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRuleDestination;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRuleDestinationPostalCode;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Component\Testing\Unit\EntityTrait;

class ShippingMethodsConfigsRuleDestinationPostalCodeTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;
    use EntityTrait;

    public function testProperties()
    {
        $properties = [
            ['id', '123'],
            ['name', 'wewfe'],
            ['destination', new ShippingMethodsConfigsRuleDestination()],
        ];

        $rule = new ShippingMethodsConfigsRuleDestinationPostalCode();
        static::assertPropertyAccessors($rule, $properties);
    }
}
