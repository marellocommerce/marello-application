<?php

namespace Marello\Bundle\RuleBundle\Entity;

interface RuleOwnerInterface
{
    /**
     * @return RuleInterface
     */
    public function getRule();
}
