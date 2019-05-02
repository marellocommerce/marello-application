<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\TaxBundle\Entity\TaxRate;

class LoadTaxRateData extends AbstractFixture
{
    const CODE_1 = 'TAX1';
    const CODE_2 = 'TAX2';
    const CODE_3 = 'TAX3';
    const CODE_4 = 'TAX4';

    const RATE_1 = 0.1;
    const RATE_2 = 0.2;
    const RATE_3 = 0.075;
    const RATE_4 = 0.9;

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
