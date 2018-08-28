<?php

namespace Marello\Bundle\PricingBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\PricingBundle\Entity\PriceType;
use Marello\Bundle\PricingBundle\Model\PriceTypeInterface;

class LoadPriceTypes extends AbstractFixture
{
    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $this->createPriceType(PriceTypeInterface::DEFAULT_PRICE, $manager);
        $this->createPriceType(PriceTypeInterface::SPECIAL_PRICE, $manager);
        $this->createPriceType(PriceTypeInterface::MSRP_PRICE, $manager);
        
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
