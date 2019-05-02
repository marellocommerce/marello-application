<?php

namespace Marello\Bundle\RuleBundle\RuleFiltration;

use Marello\Bundle\RuleBundle\Entity\RuleOwnerInterface;

interface RuleFiltrationServiceInterface
{
    /**
     * @param RuleOwnerInterface[] $ruleOwners
     * @param array $context
     * @return RuleOwnerInterface[]
     */
    public function getFilteredRuleOwners(array $ruleOwners, array $context = []);
}
