<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Entity;

use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodTypeConfig;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class ShippingMethodConfigTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $properties = [
            ['id', 1],
            ['method', 'custom'],
            ['options', ['custom' => 'test']],
            ['methodConfigsRule', new ShippingMethodsConfigsRule()],
        ];

        $entity = new ShippingMethodConfig();

        $this->assertPropertyAccessors($entity, $properties);
        $this->assertPropertyCollection($entity, 'typeConfigs', new ShippingMethodTypeConfig());
    }
}
