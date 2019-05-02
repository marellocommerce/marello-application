<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;

class MarelloEnterpriseAddressTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new MarelloEnterpriseAddress(), [
            ['id', 42],
            ['address', new MarelloAddress()],
            ['latitude', 'some string'],
            ['longitude', 'some string'],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()]
        ]);
    }
}
