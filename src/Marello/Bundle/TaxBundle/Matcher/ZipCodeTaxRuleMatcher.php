<?php

namespace Marello\Bundle\TaxBundle\Matcher;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

class ZipCodeTaxRuleMatcher extends AbstractTaxRuleMatcher
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
        $zipCode = $address->getPostalCode();

        if (null === $country || (null === $region && empty($regionText)) ||
            null === $zipCode || 0 === count($taxCodes)) {
            return null;
        }

        $taxRules = $this->getTaxRuleRepository()->findByZipCodeAndTaxCode(
            $taxCodes,
            $zipCode,
            $country,
            $region,
            $regionText
        );

        return !empty($taxRules) ? reset($taxRules) : null;
    }
}
