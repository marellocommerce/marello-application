<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\Entity;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class SalesChannelGroupTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new SalesChannelGroup(), [
            ['name', 'some string'],
            ['description', 'some string'],
            ['system', 1],
            ['organization', new Organization()]
        ]);
    }
}
