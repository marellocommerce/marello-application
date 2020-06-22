<?php

namespace Marello\Bundle\PackingBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;

class PackingSlipTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new PackingSlip(), [
            ['id', 42],
            ['order', new Order()],
            ['billingAddress', new MarelloAddress()],
            ['shippingAddress', new MarelloAddress()],
            ['customer', new Customer()],
            ['organization', new Organization()],
            ['salesChannel', new SalesChannel()],
            ['warehouse', new Warehouse()],
            ['packingSlipNumber', '#00000000042'],
            ['comment', 'some string'],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()]
        ]);
        $this->assertPropertyCollections(new PackingSlip(), [
            ['items', new PackingSlipItem()],
        ]);
    }
}
