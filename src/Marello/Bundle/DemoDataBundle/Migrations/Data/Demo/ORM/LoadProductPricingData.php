<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;

class LoadProductPricingData extends AbstractFixture implements DependentFixtureInterface
{
    protected $currency = 'USD';

    public function getDependencies() {
        return [
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData'];
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->loadProductPrices($manager);
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    public function loadProductPrices(ObjectManager $manager)
    {
        $products = $manager->getRepository('MarelloProductBundle:Product')->findAll();
        foreach ($products as $product) {
            $productRef = rand(0,145);
            $skip = ($productRef % 2 == 0) ? true : false;

            if(!$skip) {
                $this->createProductPrice($product,$manager);
            }
            continue;
        }
    }

    /**
     * @param $product
     * @param $manager
     */
    private function createProductPrice($product,$manager)
    {
        if(!count($product->getPrices()) > 0) {
            $channels = $product->getChannels();
            $max = count($product->getChannels());
            $channelCount = rand(1,$max);
            $i=1;
            foreach($channels as $channel) {
                if($i <= $channelCount) {
                    $productPrice = new ProductPrice();
                    $data = $product->getData();
                    $data[PricingAwareInterface::CHANNEL_PRICING_STATE_KEY] = true;
                    $product->setData($data);
                    $productPrice->setProduct($product);
                    $productPrice->setCurrency($this->currency);
                    $productPercentage = rand(85,95);
                    $productPriceValue = ($product->getPrice() * ($productPercentage / 100));
                    $productPrice->setValue(round((float)$productPriceValue, 2));
                    $productPrice->setChannel($channel);
                    $manager->persist($product);
                    $manager->persist($productPrice);
                }
                $i++;
            }
        }
    }
}
