<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Entity;

use Marello\Bundle\RuleBundle\Entity\Rule;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class WFARuleTest extends \PHPUnit_Framework_TestCase
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
