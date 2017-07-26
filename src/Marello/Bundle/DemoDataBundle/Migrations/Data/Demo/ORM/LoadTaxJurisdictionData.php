<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Marello\Bundle\TaxBundle\Entity\ZipCode;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

class LoadTaxJurisdictionData extends AbstractFixture
{
    const REF_1 = 'LOS_ANGELES_COUNTY';
    const REF_2 = 'ORANGE_COUNTY';
    const REF_3 = 'VENTURA_COUNTY';
    const REF_4 = 'CULVER_CITY';
    const REF_5 = 'SANTA_MONICA';

    protected $data = [
        self::REF_1 => [
            'country' => 'US',
            'state' => 'CA',
            'zip_codes' => [
                ['start' => '90001', 'end' => '90039'],
                ['start' => '90041', 'end' => '90224'],
                ['start' => '90239', 'end' => '90278'],
                ['start' => '90290', 'end' => '90296'],
                '90304',
            ],
            'description' => 'Los Angeles County',
        ],
        self::REF_2 => [
            'country' => 'US',
            'state' => 'CA',
            'zip_codes' => [
                ['start' => '90620', 'end' => '90630'],
                ['start' => '90720', 'end' => '90721'],
                ['start' => '90740', 'end' => '90743'],
                ['start' => '92602', 'end' => '92859'],
                ['start' => '92861', 'end' => '92871'],
                ['start' => '92885', 'end' => '92899'],
            ],
            'description' => 'Orange County',
        ],
        self::REF_3 => [
            'country' => 'US',
            'state' => 'CA',
            'zip_codes' => [
                ['start' => '91319', 'end' => '91320'],
                ['start' => '91358', 'end' => '91362'],
                '91377',
            ],
            'description' => 'Ventura County',
        ],
        self::REF_4 => [
            'country' => 'US',
            'state' => 'CA',
            'zip_codes' => [
                ['start' => '90232', 'end' => '90233'],
            ],
            'description' => 'Culver City',
        ],
        self::REF_5 => [
            'country' => 'US',
            'state' => 'CA',
            'zip_codes' => [
                ['start' => '90401', 'end' => '90409'],
            ],
            'description' => 'Santa Monica',
        ],
    ];

    const REFERENCE_PREFIX = 'tax_jurisdiction';

    /**
     * @param EntityManager $manager
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $ref => $data) {
            $country = $this->getCountryByIso2Code($manager, $data['country']);
            $region = $this->getRegionByCountryAndCode($manager, $country, $data['state']);
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
        Region $region,
        $zipCodes
    ) {
        $taxJurisdiction = new TaxJurisdiction();
        $taxJurisdiction->setCode($code)
            ->setDescription($description)
            ->setCountry($country)
            ->setRegion($region);

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
