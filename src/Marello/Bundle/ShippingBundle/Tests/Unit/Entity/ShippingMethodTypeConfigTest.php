<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Entity;

use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodTypeConfig;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class ShippingMethodTypeConfigTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $properties = [
            ['id', 1],
            ['type', 'custom'],
            ['options', ['custom' => 'test']],
            ['enabled', true],
            ['methodConfig', new ShippingMethodConfig()],
        ];

        $entity = new ShippingMethodTypeConfig();

        static::assertPropertyAccessors($entity, $properties);
    }
}
