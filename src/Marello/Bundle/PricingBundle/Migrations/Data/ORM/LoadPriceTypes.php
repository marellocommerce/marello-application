<?php

namespace Marello\Bundle\PricingBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\PricingBundle\Entity\PriceType;

class LoadPriceTypes extends AbstractFixture
{
    const DEFAULT_PRICE = 'default';
    const SPECIAL_PRICE = 'special';
    const MSRP_PRICE = 'msrp';

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $this->createPriceType(self::DEFAULT_PRICE, $manager);
        $this->createPriceType(self::SPECIAL_PRICE, $manager);
        $this->createPriceType(self::MSRP_PRICE, $manager);
        
        $manager->flush();
    }

    /**
     * @param string $name
     * @param ObjectManager $manager
     */
    private function createPriceType($name, ObjectManager $manager)
    {
        $price = new PriceType($name, sprintf('%s Price', ucfirst($name)));
        if (!$manager->getRepository(PriceType::class)->find($name)) {
            $manager->persist($price);
        }
        $this->setReference(sprintf('marello-%s-price-type', $name), $price);
    }
}