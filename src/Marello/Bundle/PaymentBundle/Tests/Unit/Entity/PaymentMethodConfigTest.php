<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Entity;

use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class PaymentMethodConfigTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $properties = [
            ['id', 1],
            ['method', 'custom'],
            ['options', ['custom' => 'test']],
            ['methodsConfigsRule', new PaymentMethodsConfigsRule()],
        ];

        $entity = new PaymentMethodConfig();

        $this->assertPropertyAccessors($entity, $properties);
    }
}
