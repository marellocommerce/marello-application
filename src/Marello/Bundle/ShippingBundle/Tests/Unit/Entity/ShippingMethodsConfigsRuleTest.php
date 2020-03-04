<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Entity;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Marello\Bundle\RuleBundle\Entity\Rule;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRuleDestination;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Component\Testing\Unit\EntityTrait;

class ShippingMethodsConfigsRuleTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;
    use EntityTrait;

    public function testProperties()
    {
        $properties = [
            ['id', '123'],
            ['rule', new Rule()],
            ['currency', 'USD'],
            ['organization', new Organization()]
        ];

        $rule = new ShippingMethodsConfigsRule();
        static::assertPropertyAccessors($rule, $properties);
        static::assertPropertyCollection($rule, 'methodConfigs', new ShippingMethodConfig());
        static::assertPropertyCollection($rule, 'destinations', new ShippingMethodsConfigsRuleDestination());
    }
}
