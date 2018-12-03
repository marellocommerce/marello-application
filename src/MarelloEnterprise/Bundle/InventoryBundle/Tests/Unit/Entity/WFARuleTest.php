<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\RuleBundle\Entity\Rule;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;

class WFARuleTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new WFARule(), [
            ['id', 42],
            ['strategy', 'some string'],
            ['rule', new Rule()],
            ['organization', new Organization()]
        ]);
    }
}
