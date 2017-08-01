<?php

namespace Marello\Bundle\TaxBundle\Matcher;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Entity\TaxCode;

interface TaxRuleMatcherInterface
{
    /**
     * @param AbstractAddress|null $address
     * @param array $taxCodes
     * @return TaxRule
     */
    public function match(AbstractAddress $address = null, array $taxCodes);
}
