<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\TaxBundle\Entity\TaxRate;

class LoadTaxRateData extends AbstractFixture
{
    const CODE_1 = 'LOS_ANGELES_COUNTY_SALES_TAX';
    const CODE_2 = 'ORANGE_COUNTY_SALES_TAX';
    const CODE_3 = 'VENTURA_COUNTY_SALES_TAX';
    const CODE_4 = 'CULVER_CITY_SALES_TAX';
    const CODE_5 = 'SANTA_MONICA_SALES_TAX';

    const RATE_1 = 0.09;
    const RATE_2 = 0.12;
    const RATE_3 = 0.15;
    const RATE_4 = 0.07;
    const RATE_5 = 0.18;

    const REFERENCE_PREFIX = 'tax_rate';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->createTaxRate($manager, self::CODE_1, self::RATE_1);
        $this->createTaxRate($manager, self::CODE_2, self::RATE_2);
        $this->createTaxRate($manager, self::CODE_3, self::RATE_3);
        $this->createTaxRate($manager, self::CODE_4, self::RATE_4);
        $this->createTaxRate($manager, self::CODE_5, self::RATE_5);

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
