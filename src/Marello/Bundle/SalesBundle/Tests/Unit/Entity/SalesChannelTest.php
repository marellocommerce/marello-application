<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\Entity;

use Marello\Bundle\SalesBundle\Entity\SalesChannelType;
use PHPUnit\Framework\TestCase;

use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class SalesChannelTest extends TestCase
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
            ['channelType', new SalesChannelType()],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['localization', new Localization()]
        ]);
    }
}
