<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Marello\Bundle\TaxBundle\Entity\ZipCode;
use Marello\Bundle\TaxBundle\Tests\Functional\Helper\ZipCodeTestHelper;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

class LoadTaxJurisdictionData extends AbstractFixture
{
    const DESCRIPTION = 'Tax description';

    const COUNTRY_US = 'US';
    const STATE_US_NY = 'US-NY';
    const STATE_US_AL = 'US-AL';
    const ZIP_CODE = '012345';
    const STATE_US_CA = 'US-CA';

    const REFERENCE_PREFIX = 'tax_jurisdiction';

    /**
     * @param EntityManager $manager
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->createTaxJurisdiction(
            $manager,
            LoadTaxRateData::CODE_1,
            self::DESCRIPTION,
            $this->getCountryByCode($manager, self::COUNTRY_US)
        );

        $this->createTaxJurisdiction(
            $manager,
            LoadTaxRateData::CODE_2,
            self::DESCRIPTION,
            $this->getCountryByCode($manager, self::COUNTRY_US),
            $this->getRegionByCode($manager, self::STATE_US_NY)
        );

        $this->createTaxJurisdiction(
            $manager,
            LoadTaxRateData::CODE_3,
            self::DESCRIPTION,
            $this->getCountryByCode($manager, self::COUNTRY_US),
            $this->getRegionByCode($manager, self::STATE_US_CA)
        );

        $this->createTaxJurisdiction(
            $manager,
            LoadTaxRateData::CODE_4,
            self::DESCRIPTION,
            $this->getCountryByCode($manager, self::COUNTRY_US),
            $this->getRegionByCode($manager, self::STATE_US_AL),
            null,
            ZipCodeTestHelper::getSingleValueZipCode(self::ZIP_CODE)
        );

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string $code
     * @param string $description
     * @param Country $country
     * @param Region $region
     * @param string $regionText
     * @param ZipCode $zipCode
     * @return TaxJurisdiction
     * @throws \Doctrine\ORM\ORMException
     */
    protected function createTaxJurisdiction(
        ObjectManager $manager,
        $code,
        $description,
        Country $country,
        Region $region = null,
        $regionText = null,
        ZipCode $zipCode = null
    ) {
        $taxJurisdiction = new TaxJurisdiction();
        $taxJurisdiction->setCode($code);
        $taxJurisdiction->setDescription($description);
        $taxJurisdiction->setCountry($country);

        if ($region) {
            $taxJurisdiction->setRegion($region);
        } elseif ($regionText) {
            $taxJurisdiction->setRegionText($regionText);
        }

        if ($zipCode) {
            $taxJurisdiction->addZipCode($zipCode);
        }

        $manager->persist($taxJurisdiction);
        $this->addReference(static::REFERENCE_PREFIX . '.' . $code, $taxJurisdiction);

        return $taxJurisdiction;
    }

    /**
     * @param ObjectManager $manager
     * @param string $code
     * @return Country
     */
    public static function getCountryByCode(ObjectManager $manager, $code)
    {
        /** @var EntityManagerInterface $manager */
        return $manager->getReference('OroAddressBundle:Country', $code);
    }

    /**
     * @param ObjectManager $manager
     * @param string $code
     * @return Region
     */
    public static function getRegionByCode(ObjectManager $manager, $code)
    {
        /** @var EntityManagerInterface $manager */
        return $manager->getReference('OroAddressBundle:Region', $code);
    }
}
