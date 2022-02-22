<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\TaxBundle\Entity\TaxRate;

class LoadTaxRateData extends AbstractFixture
{
    const CODE_1 = 'GERMANY_SALES_TAX';
    const CODE_2 = 'GREAT_BRITAIN_SALES_TAX';
    const CODE_3 = 'FRANCE_SALES_TAX';
    const CODE_4 = 'ALABAMA_SALES_TAX';
    const CODE_5 = 'ORANGE_COUNTY_SALES_TAX';
    const CODE_6 = 'VENTURA_COUNTY_SALES_TAX';
    const CODE_7 = 'CULVER_CITY_SALES_TAX';
    const CODE_8 = 'SANTA_MONICA_SALES_TAX';
    const CODE_9 = 'LOS_ANGELES_COUNTY_SALES_TAX';

    const RATE_1 = 0.19;
    const RATE_2 = 0.20;
    const RATE_3 = 0.20;
    const RATE_4 = 0.04;
    const RATE_5 = 0.09;
    const RATE_6 = 0.08;
    const RATE_7 = 0.075;
    const RATE_8 = 0.095;
    const RATE_9 = 0.095;

    const REFERENCE_PREFIX = 'tax_rate';

    /** @var array $data */
    protected $data = [
        self::CODE_1 => self::RATE_1,
        self::CODE_2 => self::RATE_2,
        self::CODE_3 => self::RATE_3,
        self::CODE_4 => self::RATE_4,
        self::CODE_5 => self::RATE_5,
        self::CODE_6 => self::RATE_6,
        self::CODE_7 => self::RATE_7,
        self::CODE_8 => self::RATE_8,
        self::CODE_9 => self::RATE_9
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $code => $rate) {
            $this->createTaxRate($manager, $code, $rate);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string        $code
     * @param int           $rate
     * @return TaxRate
     */
    protected function createTaxRate(ObjectManager $manager, $code, $rate)
    {
        $tax = new TaxRate();
        $tax->setCode($code);
        $tax->setRate($rate);

        $manager->persist($tax);
        $this->addReference(self::REFERENCE_PREFIX . '.' . $code, $tax);

        return $tax;
    }
}
