<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\TaxBundle\Entity\TaxRate;

class LoadTaxRateData extends AbstractFixture
{
    const CODE_1 = 'FRANCE_SALES_TAX';
    const CODE_2 = 'GREAT_BRITAIN_SALES_TAX';
    const CODE_3 = 'GERMANY_SALES_TAX';

    const RATE_1 = 0.19;
    const RATE_2 = 0.20;
    const RATE_3 = 0.20;

    const REFERENCE_PREFIX = 'tax_rate';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->createTaxRate($manager, self::CODE_1, self::RATE_1);
        $this->createTaxRate($manager, self::CODE_2, self::RATE_2);
        $this->createTaxRate($manager, self::CODE_3, self::RATE_3);

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
