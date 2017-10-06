<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Tests\Unit\Entity;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class MarelloEnterpriseAddressTest extends \PHPUnit_Framework_TestCase
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
