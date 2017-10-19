<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

use Marello\Bundle\TaxBundle\Entity\ZipCode;
use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;

class LoadTaxJurisdictionData extends AbstractFixture
{
    const REF_1 = 'FRANCE';
    const REF_2 = 'GREAT_BRITAIN';
    const REF_3 = 'GERMANY';
    const REFERENCE_PREFIX = 'tax_jurisdiction';

    /** @var array $data */
    protected $data = [
        self::REF_1 => [
            'country' => 'FR',
            'state' => null,
            'zip_codes' => null,
            'description' => 'France',
        ],
        self::REF_2 => [
            'country' => 'GB',
            'state' => null,
            'zip_codes' => null,
            'description' => 'United Kingdom',
        ],
        self::REF_3 => [
            'country' => 'DE',
            'state' => null,
            'zip_codes' => null,
            'description' => 'Germany',
        ],
    ];

    /**
     * @param EntityManager $manager
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $ref => $data) {
            $country = $this->getCountryByIso2Code($manager, $data['country']);
            $region = null;
            if ($data['state'] !== null) {
                $region = $this->getRegionByCountryAndCode($manager, $country, $data['state']);
            }
            $taxJurisdiction =  $this->createTaxJurisdiction(
                $manager,
                $ref,
                $data['description'],
                $country,
                $region,
                $data['zip_codes']
            );
            $this->addReference(static::REFERENCE_PREFIX . '.' . $ref, $taxJurisdiction);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string $code
     * @param string $description
     * @param Country $country
     * @param Region $region
     * @param array $zipCodes
     * @return TaxJurisdiction
     * @throws \Doctrine\ORM\ORMException
     */
    protected function createTaxJurisdiction(
        ObjectManager $manager,
        $code,
        $description,
        Country $country,
        Region $region = null,
        $zipCodes
    ) {
        $taxJurisdiction = new TaxJurisdiction();
        $taxJurisdiction->setCode($code)
            ->setDescription($description)
            ->setCountry($country)
            ->setRegion($region);

        if (is_array($zipCodes)) {
            foreach ($zipCodes as $data) {
                $zipCode = new ZipCode();
                if (is_array($data)) {
                    $zipCode->setZipRangeStart($data['start']);
                    $zipCode->setZipRangeEnd($data['end']);
                } else {
                    $zipCode->setZipCode($data);
                }

                $taxJurisdiction->addZipCode($zipCode);
            }
        }

        $manager->persist($taxJurisdiction);

        return $taxJurisdiction;
    }

    /**
     * @param ObjectManager $manager
     * @param string $iso2Code
     *
     * @return Country|null
     */
    private function getCountryByIso2Code(ObjectManager $manager, $iso2Code)
    {
        return $manager->getRepository('OroAddressBundle:Country')->findOneBy(['iso2Code' => $iso2Code]);
    }

    /**
     * @param ObjectManager $manager
     * @param Country $country
     * @param string $code
     *
     * @return Region|null
     */
    private function getRegionByCountryAndCode(ObjectManager $manager, Country $country, $code)
    {
        return $manager->getRepository('OroAddressBundle:Region')->findOneBy(['country' => $country, 'code' => $code]);
    }
}
