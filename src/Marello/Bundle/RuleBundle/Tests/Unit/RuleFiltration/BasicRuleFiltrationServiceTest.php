<?php

namespace Marello\Bundle\RuleBundle\Tests\Unit\RuleFiltration;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\RuleBundle\Entity\RuleOwnerInterface;
use Marello\Bundle\RuleBundle\RuleFiltration\BasicRuleFiltrationService;

class BasicRuleFiltrationServiceTest extends TestCase
{
    /**
     * @var BasicRuleFiltrationService
     */
    private $service;

    protected function setUp(): void
    {
        $this->service = new BasicRuleFiltrationService();
    }

    public function testGetFilteredRuleOwners()
    {
        $context = [];

        $ruleOwners = [
            $this->createPartialMock(RuleOwnerInterface::class, ['getRule']),
            $this->createPartialMock(RuleOwnerInterface::class, ['getRule']),
        ];

        static::assertEquals($ruleOwners, $this->service->getFilteredRuleOwners($ruleOwners, $context));
    }
}
