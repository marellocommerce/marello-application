<?php

namespace Marello\Bundle\TaxBundle\Matcher;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

class CompositeTaxRuleMatcher implements TaxRuleMatcherInterface
{
    const CACHE_KEY_DELIMITER = ':';

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @var TaxRuleMatcherInterface[]
     */
    private $matchers = [];

    /**
     * @param TaxRuleMatcherInterface $matcher
     */
    public function addMatcher(TaxRuleMatcherInterface $matcher)
    {
        $this->matchers[] = $matcher;
    }

    /**
     * {@inheritdoc}
     */
    public function match(AbstractAddress $address = null, array $taxCodes)
    {
        if (null === $address || null === $address->getCountry() || 0 === count($taxCodes)) {
            return [];
        }

        $cacheKey = $this->getCacheKey($address, $taxCodes);
        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }
        foreach ($this->matchers as $matcher) {
            $taxRule = $matcher->match($address, $taxCodes);
            if ($taxRule) {
                $this->cache[$cacheKey] = $taxRule;

                return $this->cache[$cacheKey];
            }
        }
        
        return null;
    }

    /**
     * @param AbstractAddress $address
     * @param array $taxCodes
     * @return string
     */
    protected function getCacheKey(AbstractAddress $address, array $taxCodes)
    {
        $countryCode = $address->getCountryIso2();
        $regionCode = $address->getRegionCode() ? : $address->getRegionText();
        $zipCode = $address->getPostalCode();
        $taxCodesHash = md5(json_encode($taxCodes));

        return sprintf('%s:%s:%s:%s', $countryCode, $regionCode, $zipCode, $taxCodesHash);
    }
}
