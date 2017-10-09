<?php

namespace Marello\Bundle\TaxBundle\Matcher;

use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

interface TaxRuleMatcherInterface
{
    /**
     * @param AbstractAddress|null $address
     * @param array $taxCodes
     * @return TaxRule
     */
    public function match(AbstractAddress $address = null, array $taxCodes);
}
