<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Entity;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Marello\Bundle\RuleBundle\Entity\Rule;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRuleDestination;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Component\Testing\Unit\EntityTrait;

class PaymentMethodsConfigsRuleTest extends \PHPUnit\Framework\TestCase
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

        $rule = new PaymentMethodsConfigsRule();
        static::assertPropertyAccessors($rule, $properties);
        static::assertPropertyCollection($rule, 'methodConfigs', new PaymentMethodConfig());
        static::assertPropertyCollection($rule, 'destinations', new PaymentMethodsConfigsRuleDestination());
    }
}
