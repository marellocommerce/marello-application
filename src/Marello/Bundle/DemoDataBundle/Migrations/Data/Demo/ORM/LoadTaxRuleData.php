<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;

class LoadTaxRuleData extends AbstractFixture implements DependentFixtureInterface
{
    const TAX_RULE_1 = 'TAX_RULE_1';
    const TAX_RULE_2 = 'TAX_RULE_2';
    const TAX_RULE_3 = 'TAX_RULE_3';
    const TAX_RULE_4 = 'TAX_RULE_4';
    const TAX_RULE_5 = 'TAX_RULE_5';
    const TAX_RULE_6 = 'TAX_RULE_6';
    const TAX_RULE_7 = 'TAX_RULE_7';
    const TAX_RULE_8 = 'TAX_RULE_8';
    const TAX_RULE_9 = 'TAX_RULE_9';

    const REFERENCE_PREFIX = 'tax_rule';

    /** @var array $data */
    protected $data = [
        self::TAX_RULE_1 => [
            'tax_code' => LoadTaxCodeData::TAXCODE_1_REF,
            'tax_jurisdiction' => LoadTaxJurisdictionData::REF_1,
            'tax_rate' => LoadTaxRateData::CODE_1
        ],
        self::TAX_RULE_2 => [
            'tax_code' => LoadTaxCodeData::TAXCODE_2_REF,
            'tax_jurisdiction' => LoadTaxJurisdictionData::REF_2,
            'tax_rate' => LoadTaxRateData::CODE_2
        ],
        self::TAX_RULE_3 => [
            'tax_code' => LoadTaxCodeData::TAXCODE_3_REF,
            'tax_jurisdiction' => LoadTaxJurisdictionData::REF_3,
            'tax_rate' => LoadTaxRateData::CODE_3
        ],
        self::TAX_RULE_4 => [
            'tax_code' => LoadTaxCodeData::TAXCODE_4_REF,
            'tax_jurisdiction' => LoadTaxJurisdictionData::REF_4,
            'tax_rate' => LoadTaxRateData::CODE_4
        ],
        self::TAX_RULE_5 => [
            'tax_code' => LoadTaxCodeData::TAXCODE_4_REF,
            'tax_jurisdiction' => LoadTaxJurisdictionData::REF_5,
            'tax_rate' => LoadTaxRateData::CODE_5
        ],
        self::TAX_RULE_6 => [
            'tax_code' => LoadTaxCodeData::TAXCODE_4_REF,
            'tax_jurisdiction' => LoadTaxJurisdictionData::REF_6,
            'tax_rate' => LoadTaxRateData::CODE_6
        ],
        self::TAX_RULE_7 => [
            'tax_code' => LoadTaxCodeData::TAXCODE_4_REF,
            'tax_jurisdiction' => LoadTaxJurisdictionData::REF_7,
            'tax_rate' => LoadTaxRateData::CODE_7
        ],
        self::TAX_RULE_8 => [
            'tax_code' => LoadTaxCodeData::TAXCODE_4_REF,
            'tax_jurisdiction' => LoadTaxJurisdictionData::REF_8,
            'tax_rate' => LoadTaxRateData::CODE_8
        ],
        self::TAX_RULE_9 => [
            'tax_code' => LoadTaxCodeData::TAXCODE_4_REF,
            'tax_jurisdiction' => LoadTaxJurisdictionData::REF_9,
            'tax_rate' => LoadTaxRateData::CODE_9
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadTaxCodeData',
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadTaxRateData',
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadTaxJurisdictionData',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $ref => $data) {
            /** @var TaxCode $taxCode */
            $taxCode = $this->getReference($data['tax_code']);
            /** @var TaxRate $taxRate */
            $taxRate = $this->getReference(LoadTaxRateData::REFERENCE_PREFIX . '.' . $data['tax_rate']);
            /** @var TaxJurisdiction $taxJurisdiction */
            $taxJurisdiction = $this->getReference(
                LoadTaxJurisdictionData::REFERENCE_PREFIX . '.' . $data['tax_jurisdiction']
            );

            $taxRule = $this->createTaxRule($manager, $taxCode, $taxRate, $taxJurisdiction);

            $this->addReference(static::REFERENCE_PREFIX . '.' . $ref, $taxRule);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param TaxCode $taxCode
     * @param TaxRate $taxRate
     * @param TaxJurisdiction $taxJurisdiction
     * @return TaxRule
     */
    protected function createTaxRule(
        ObjectManager $manager,
        TaxCode $taxCode,
        TaxRate $taxRate,
        TaxJurisdiction $taxJurisdiction
    ) {
        $taxRule = new TaxRule();
        $taxRule
            ->setTaxCode($taxCode)
            ->setTaxRate($taxRate)
            ->setTaxJurisdiction($taxJurisdiction);

        $manager->persist($taxRule);

        return $taxRule;
    }
}
