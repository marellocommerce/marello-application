<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Matcher;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Matcher\ZipCodeTaxRuleMatcher;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

class ZipCodeTaxRuleMatcherTest extends AbstractTaxRuleMatcherTest
{
    const POSTAL_CODE = '02097';

    /**
     * @var ZipCodeTaxRuleMatcher
     */
    protected $matcher;

    protected function setUp()
    {
        parent::setUp();

        $this->matcher = new ZipCodeTaxRuleMatcher($this->doctrineHelper);
    }

    /**
     * @dataProvider matchProvider
     * @param string $taxCode
     * @param Country $country
     * @param Region $region
     * @param string $regionText
     * @param TaxRule $zipCodeMatcherTaxRules
     * @param TaxRule $expected
     * @param int $callRepositoryTimes
     */
    public function testMatch(
        $taxCode,
        $country,
        $region,
        $regionText,
        $zipCodeMatcherTaxRules,
        $expected,
        $callRepositoryTimes
    ) {
        $address = (new Address())
            ->setPostalCode(self::POSTAL_CODE)
            ->setCountry($country)
            ->setRegion($region)
            ->setRegionText($regionText);

        $taxCodes = [];
        if ($taxCode) {
            $taxCodes[] = $this->getEntity(TaxCode::class, ['code' => $taxCode]);
        }

        $this->taxRuleRepository
            ->expects($this->exactly($callRepositoryTimes))
            ->method('findByZipCodeAndTaxCode')
            ->with(
                $taxCodes,
                self::POSTAL_CODE,
                $country,
                $region,
                $regionText
            )
            ->willReturn($zipCodeMatcherTaxRules);

        $this->assertEquals($expected, $this->matcher->match($address, $taxCodes));
    }

    /**
     * @return array
     */
    public function matchProvider()
    {
        $country = new Country('US');
        $region = new Region('US-NY');
        $regionText = 'Alaska';

        $zipCodeMatcherTaxRule = $this->getTaxRule(1);

        return [
            'with region' => [
                'taxCode' => 'TAX_CODE',
                'country' => $country,
                'region' => $region,
                'regionText' => '',
                'zipCodeMatcherRules' => [$zipCodeMatcherTaxRule],
                'expected' => $zipCodeMatcherTaxRule,
                'callRepositoryTimes' => 1
            ],
            'with regionText' => [
                'taxCode' => 'TAX_CODE',
                'country' => $country,
                'region' => null,
                'regionText' => $regionText,
                'zipCodeMatcherRules' => [$zipCodeMatcherTaxRule],
                'expected' => $zipCodeMatcherTaxRule,
                'callRepositoryTimes' => 1
            ],
            'without country' => [
                'axCode' => 'TAX_CODE',
                'country' => null,
                'region' => $region,
                'regionText' => $regionText,
                'zipCodeMatcherRules' => [],
                'expected' => null,
                'callRepositoryTimes' => 0
            ],
            'without tax code' => [
                'taxCode' => null,
                'country' => $country,
                'region' => $region,
                'regionText' => $regionText,
                'zipCodeMatcherRules' => [],
                'expected' => null,
                'callRepositoryTimes' => 0
            ],
            'without region and regionText' => [
                'taxCode' => 'TAX_CODE',
                'country' => $country,
                'region' => null,
                'regionText' => '',
                'zipCodeMatcherRules' => [],
                'expected' => null,
                'callRepositoryTimes' => 0
            ],
        ];
    }
}
