<?php

namespace Marello\Bundle\PricingBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList;
use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\Product;

class CopyExistingPricesToPriceLists extends AbstractFixture
{
    const DEFAULT_PRICE = 'default';
    const SPECIAL_PRICE = 'special';
    const MSRP_PRICE = 'msrp';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $products = $manager->getRepository(Product::class)->findAll();
        /** @var Product $product */
        foreach ($products as $product) {
            $prices = $manager->getRepository(ProductPrice::class)->findBy(['product' => $product]);
            if (count($prices) > 0) {
                $this->createAssembledPriceList($prices, $manager);
            }

            $channelPrices = $manager->getRepository(ProductChannelPrice::class)->findBy(['product' => $product]);
            if (count($prices) > 0) {
                $this->createAssembledChannelPriceList($channelPrices, $manager);
            }
        }
        
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     * @param array $prices
     * @param ObjectManager $manager
     */
    private function createAssembledPriceList($prices, ObjectManager $manager)
    {
        /** @var ProductPrice $price */
        foreach ($prices as $price) {
            $assembledPriceList = new AssembledPriceList();
            $assembledPriceList->setProduct($price->getProduct());
            $assembledPriceList->setCurrency($price->getCurrency());
            $assembledPriceList->setDefaultPrice($price);
            $manager->persist($assembledPriceList);
        }
    }


    /**
     * {@inheritdoc}
     * @param array $prices
     * @param ObjectManager $manager
     */
    private function createAssembledChannelPriceList($prices, ObjectManager $manager)
    {
        /** @var ProductChannelPrice $price */
        foreach ($prices as $price) {
            $assembledChannelPriceList = new AssembledChannelPriceList();
            $assembledChannelPriceList->setProduct($price->getProduct());
            $assembledChannelPriceList->setChannel($price->getChannel());
            $assembledChannelPriceList->setCurrency($price->getCurrency());
            $assembledChannelPriceList->setDefaultPrice($price);
            $manager->persist($assembledChannelPriceList);
        }
    }
}
