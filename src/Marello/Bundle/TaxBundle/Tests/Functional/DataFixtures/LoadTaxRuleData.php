<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Marello\Bundle\TaxBundle\Entity\TaxRule;

class LoadTaxRuleData extends AbstractFixture implements DependentFixtureInterface
{
    const TAX_RULE_1 = 'TAX_RULE_1';
    const TAX_RULE_2 = 'TAX_RULE_2';
    const TAX_RULE_3 = 'TAX_RULE_3';
    const TAX_RULE_4 = 'TAX_RULE_4';

    const REFERENCE_PREFIX = 'tax_rule';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData',
            'Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxRateData',
            'Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxJurisdictionData',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var TaxCode $taxCode */
        $taxCode = $this->getReference(LoadTaxCodeData::TAXCODE_0_REF);

        /** @var TaxRate $taxRate */
        $taxRate = $this->getReference(LoadTaxRateData::REFERENCE_PREFIX . '.' . LoadTaxRateData::CODE_1);

        /** @var TaxJurisdiction $taxJurisdiction */
        $taxJurisdiction = $this->getReference(
            LoadTaxJurisdictionData::REFERENCE_PREFIX . '.' . LoadTaxRateData::CODE_1
        );

        /** @var TaxJurisdiction $taxJurisdiction2 */
        $taxJurisdiction2 = $this->getReference(
            LoadTaxJurisdictionData::REFERENCE_PREFIX . '.' . LoadTaxRateData::CODE_2
        );

        /** @var TaxJurisdiction $taxJurisdiction3 */
        $taxJurisdiction3 = $this->getReference(
            LoadTaxJurisdictionData::REFERENCE_PREFIX . '.' . LoadTaxRateData::CODE_3
        );

        /** @var TaxJurisdiction $taxJurisdiction4 */
        $taxJurisdiction4 = $this->getReference(
            LoadTaxJurisdictionData::REFERENCE_PREFIX . '.' . LoadTaxRateData::CODE_4
        );

        $this->createTaxRule(
            $manager,
            $taxCode,
            $taxRate,
            $taxJurisdiction,
            self::TAX_RULE_1
        );

        $this->createTaxRule(
            $manager,
            $taxCode,
            $taxRate,
            $taxJurisdiction2,
            self::TAX_RULE_2
        );

        $this->createTaxRule(
            $manager,
            $taxCode,
            $taxRate,
            $taxJurisdiction3,
            self::TAX_RULE_3
        );

        $this->createTaxRule(
            $manager,
            $taxCode,
            $taxRate,
            $taxJurisdiction4,
            self::TAX_RULE_4
        );

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param TaxCode $taxCode
     * @param TaxRate $taxRate
     * @param TaxJurisdiction $taxJurisdiction
     * @param string $reference
     * @return TaxRule
     */
    protected function createTaxRule(
        ObjectManager $manager,
        TaxCode $taxCode,
        TaxRate $taxRate,
        TaxJurisdiction $taxJurisdiction,
        $reference
    ) {
        $taxRule = new TaxRule();
        $taxRule
            ->setTaxCode($taxCode)
            ->setTaxRate($taxRate)
            ->setTaxJurisdiction($taxJurisdiction);

        $manager->persist($taxRule);
        $this->addReference(static::REFERENCE_PREFIX . '.' . $reference, $taxRule);

        return $taxRule;
    }
}
