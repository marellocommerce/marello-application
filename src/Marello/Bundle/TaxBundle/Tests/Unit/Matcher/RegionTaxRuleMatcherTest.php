<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Matcher;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Matcher\RegionTaxRuleMatcher;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

class RegionTaxRuleMatcherTest extends AbstractTaxRuleMatcherTest
{
    /**
     * @var RegionTaxRuleMatcher
     */
    protected $matcher;

    protected function setUp()
    {
        parent::setUp();

        $this->matcher = new RegionTaxRuleMatcher($this->doctrineHelper);
    }

    /**
     * @dataProvider matchProvider
     * @param string $taxCode
     * @param Country $country
     * @param Region $region
     * @param string $regionText
     * @param TaxRule[] $regionTaxRules
     * @param TaxRule[] $expected
     * @param int $callRepositoryTimes
     */
    public function testMatch(
        $taxCode,
        $country,
        $region,
        $regionText,
        $regionTaxRules,
        $expected,
        $callRepositoryTimes
    ) {
        $address = (new Address())
            ->setCountry($country)
            ->setRegion($region)
            ->setRegionText($regionText);

        $taxCodes = [];
        if ($taxCode) {
            $taxCodes[] = $this->getEntity(TaxCode::class, ['code' => $taxCode]);
        }

        $this->taxRuleRepository
            ->expects($this->exactly($callRepositoryTimes))
            ->method('findByRegionAndTaxCode')
            ->with($taxCodes, $country, $region, $regionText)
            ->willReturn($regionTaxRules);

        $this->assertEquals($expected, $this->matcher->match($address, $taxCodes));
    }

    /**
     * @return array
     */
    public function matchProvider()
    {
        $country = new Country('US');
        $region = new Region('US-AL');
        $regionText = 'Alaska';

        $repositoryTaxRules = [
            $this->getTaxRule(1),
            $this->getTaxRule(2),
        ];

        return [
            'with country and region and taxCode' => [
                'taxCode' => 'TAX_CODE',
                'country' => $country,
                'region' => $region,
                'regionText' => '',
                'regionTaxRules' => $repositoryTaxRules,
                'expected' => $repositoryTaxRules[0],
                'callRepositoryTimes' => 1
            ],
            'with country and regionText and taxCode' => [
                'taxCode' => 'TAX_CODE',
                'country' => $country,
                'region' => null,
                'regionText' => $regionText,
                'regionTaxRules' => $repositoryTaxRules,
                'expected' => $repositoryTaxRules[0],
                'callRepositoryTimes' => 1
            ],
            'without tax code' => [
                'taxCode' => null,
                'country' => $country,
                'region' => $region,
                'regionText' => $regionText,
                'regionTaxRules' => [],
                'expected' => null,
                'callRepositoryTimes' => 0
            ],
            'without country' => [
                'taxCode' => 'TAX_CODE',
                'country' => null,
                'region' => $region,
                'regionText' => $regionText,
                'regionTaxRules' => [],
                'expected' => null,
                'callRepositoryTimes' => 0
            ],
            'without region and region text' => [
                'taxCode' => 'TAX_CODE',
                'country' => $country,
                'region' => null,
                'regionText' => '',
                'regionTaxRules' => [],
                'expected' => null,
                'callRepositoryTimes' => 0
            ],
        ];
    }
}
