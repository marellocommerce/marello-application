<?php

namespace Marello\Bundle\TaxBundle\Matcher;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

class RegionTaxRuleMatcher extends AbstractTaxRuleMatcher
{
    /**
     * {@inheritdoc}
     */
    public function match(AbstractAddress $address = null, array $taxCodes)
    {
        if (null === $address) {
            return null;
        }
        $country = $address->getCountry();
        $region = $address->getRegion();
        $regionText = $address->getRegionText();

        if (null === $country || (null === $region && empty($regionText)) || 0 === count($taxCodes)) {
            return null;
        }
        $taxRules = $this->getTaxRuleRepository()->findByRegionAndTaxCode($taxCodes, $country, $region, $regionText);
        
        return !empty($taxRules) ? reset($taxRules) : null;
    }
}
