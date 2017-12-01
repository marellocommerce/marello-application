<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Matcher;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Matcher\CountryTaxRuleMatcher;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;

class CountryTaxRuleMatcherTest extends AbstractTaxRuleMatcherTest
{
    /**
     * @var CountryTaxRuleMatcher
     */
    protected $matcher;

    protected function setUp()
    {
        parent::setUp();

        $this->matcher = new CountryTaxRuleMatcher($this->doctrineHelper);
    }

    /**
     * @dataProvider matchProvider
     * @param TaxRule[] $expected
     * @param Country $country
     * @param string $taxCode
     * @param TaxRule[] $taxRules
     * @param int $callRepositoryTimes
     */
    public function testMatch($expected, $country, $taxCode, $taxRules, $callRepositoryTimes)
    {
        $address = (new Address())
            ->setCountry($country);

        $taxCodes = [];
        if ($taxCode) {
            $taxCodes[] = $this->getEntity(TaxCode::class, ['code' => $taxCode]);
        }

        $this->taxRuleRepository
            ->expects($this->exactly($callRepositoryTimes))
            ->method('findByCountryAndTaxCode')
            ->with($taxCodes, $country)
            ->willReturn($taxRules);

        $this->assertEquals($expected, $this->matcher->match($address, $taxCodes));
    }

    /**
     * @return array
     */
    public function matchProvider()
    {
        $taxRules = [
            new TaxRule(),
            new TaxRule(),
        ];

        return [
            'address with country and tax code' => [
                'expected' => $taxRules[0],
                'country' => new Country('US'),
                'taxCode' => 'TAX_CODE',
                'taxRules' => $taxRules,
                'callRepositoryTimes' => 1
            ],
            'address without country' => [
                'expected' => null,
                'country' => null,
                'taxCode' => 'TAX_CODE',
                'taxRules' => [],
                'callRepositoryTimes' => 0
            ],
            'address without tax code' => [
                'expected' => null,
                'country' => new Country('US'),
                'taxCode' => null,
                'taxRules' => [],
                'callRepositoryTimes' => 0
            ],
        ];
    }
}
