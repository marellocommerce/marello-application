<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\Entity;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class SalesChannelTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new SalesChannel(), [
            ['id', 42],
            ['name', 'some string'],
            ['code', 'some string'],
            ['currency', 'some string'],
            ['active', 1],
            ['default', 1],
            ['owner', new Organization()],
            ['channelType', 'some string'],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['localization', new Localization()],
            ['locale', 'some string']
        ]);
    }
}
