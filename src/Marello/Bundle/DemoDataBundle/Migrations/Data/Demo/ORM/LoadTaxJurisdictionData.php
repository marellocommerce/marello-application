<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

use Marello\Bundle\TaxBundle\Entity\ZipCode;
use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;

class LoadTaxJurisdictionData extends AbstractFixture
{
    const REF_1 = 'GERMANY';
    const REF_2 = 'GREAT_BRITAIN';
    const REF_3 = 'FRANCE';
    const REF_4 = 'ALABAMA';
    const REF_5 = 'ORANGE_COUNTRY';
    const REF_6 = 'VENTURA_COUNTY';
    const REF_7 = 'CULVER_CITY';
    const REF_8 = 'SANTA_MONICA';
    const REF_9 = 'LOS_ANGELES_COUNTY';

    const REFERENCE_PREFIX = 'tax_jurisdiction';

    /** @var array $data */
    protected $data = [
        self::REF_1 => [
            'country' => 'DE',
            'state' => null,
            'zip_codes' => null,
            'description' => 'Germany',
        ],
        self::REF_2 => [
            'country' => 'GB',
            'state' => null,
            'zip_codes' => null,
            'description' => 'United Kingdom',
        ],
        self::REF_3 => [
            'country' => 'FR',
            'state' => null,
            'zip_codes' => null,
            'description' => 'France',
        ],
        self::REF_4 => [
            'country' => 'US',
            'state' => 'AL',
            'zip_codes' => [
                '36043',
                '36064',
                [
                    'start' => '36101',
                    'end' => '36121'
                ],
                [
                    'start' => '36123',
                    'end' => '36125'
                ],
                [
                    'start' => '36130',
                    'end' => '36135'
                ],
                [
                    'start' => '36140',
                    'end' => '36142'
                ]
            ],
            'description' => 'Alabama',
        ],
        self::REF_5 => [
            'country' => 'US',
            'state' => 'CA',
            'zip_codes' => [
                [
                    'start' => '92885',
                    'end' => '92899'
                ],
                [
                    'start' => '92861',
                    'end' => '92871'
                ],
                [
                    'start' => '92602',
                    'end' => '92859'
                ],
                [
                    'start' => '90740',
                    'end' => '90743'
                ],
                [
                    'start' => '90720',
                    'end' => '90721'
                ],
                [
                    'start' => '90620',
                    'end' => '90630'
                ]
            ],
            'description' => 'Orange County',
        ],
        self::REF_6 => [
            'country' => 'US',
            'state' => 'CA',
            'zip_codes' => [
                '93042',
                '93040',
                '91377',
                [
                    'start' => '93094',
                    'end' => '93099'
                ],
                [
                    'start' => '93060',
                    'end' => '93066'
                ],
                [
                    'start' => '93015',
                    'end' => '93024'
                ],
                [
                    'start' => '93001',
                    'end' => '93012'
                ],
                [
                    'start' => '91358',
                    'end' => '91362'
                ],
                [
                    'start' => '91319',
                    'end' => '91320'
                ]
            ],
            'description' => 'Ventura County',
        ],
        self::REF_7 => [
            'country' => 'US',
            'state' => 'CA',
            'zip_codes' => [
                [
                    'start' => '90232',
                    'end' => '90233'
                ]
            ],
            'description' => 'Culver City',
        ],
        self::REF_8 => [
            'country' => 'US',
            'state' => 'CA',
            'zip_codes' => [
                [
                    'start' => '90401',
                    'end' => '90409'
                ]
            ],
            'description' => 'Santa Monica',
        ],
        self::REF_9 => [
            'country' => 'US',
            'state' => 'CA',
            'zip_codes' => [
                '93510',
                '90304',
                [
                    'start' => '93550',
                    'end' => '93553'
                ],
                [
                    'start' => '93543',
                    'end' => '93544'
                ],
                [
                    'start' => '93532',
                    'end' => '93539'
                ],
                [
                    'start' => '91788',
                    'end' => '91899'
                ],
                [
                    'start' => '91765',
                    'end' => '91780'
                ],
                [
                    'start' => '91754',
                    'end' => '91756'
                ],
                [
                    'start' => '91744',
                    'end' => '91750'
                ],
                [
                    'start' => '91740',
                    'end' => '91741'
                ],
                [
                    'start' => '91711',
                    'end' => '91724'
                ],
                [
                    'start' => '91702',
                    'end' => '91706'
                ],
                [
                    'start' => '91380',
                    'end' => '91618'
                ],
                [
                    'start' => '91364',
                    'end' => '91376'
                ],
                [
                    'start' => '91342',
                    'end' => '91357'
                ],
                [
                    'start' => '91321',
                    'end' => '91337'
                ],
                [
                    'start' => '90744',
                    'end' => '91316'
                ],
                [
                    'start' => '90723',
                    'end' => '90734'
                ],
                [
                    'start' => '90706',
                    'end' => '90717'
                ],
                [
                    'start' => '90701',
                    'end' => '90703'
                ],
                [
                    'start' => '90670',
                    'end' => '90671'
                ],
                [
                    'start' => '90640',
                    'end' => '90652'
                ],
                [
                    'start' => '90501',
                    'end' => '90609'
                ],
                [
                    'start' => '90290',
                    'end' => '90296'
                ],
                [
                    'start' => '90239',
                    'end' => '90278'
                ],
                [
                    'start' => '90041',
                    'end' => '90224'
                ],
                [
                    'start' => '90001',
                    'end' => '90039'
                ]
            ],
            'description' => 'Los Angeles County',
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
