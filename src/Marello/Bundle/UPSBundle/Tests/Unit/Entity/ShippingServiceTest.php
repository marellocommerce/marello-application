<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use Marello\Bundle\UPSBundle\Entity\ShippingService;

class ShippingServiceTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        static::assertPropertyAccessors(new ShippingService(), [
            ['code', 'some code'],
            ['description', 'some description'],
            ['country', new Country('US')]
        ]);
    }

    public function testToString()
    {
        $entity = new ShippingService();
        $entity->setCode('03')->setDescription('UPS Ground');
        static::assertEquals('UPS Ground', (string)$entity);
    }
}
