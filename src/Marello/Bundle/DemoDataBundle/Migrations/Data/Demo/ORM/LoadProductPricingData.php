<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;

class LoadProductPricingData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var string $currency */
    protected $currency = 'USD';

    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @return array
     */
    public function getDependencies()
    {
        return ['Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData'];
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadProductPrices();
    }

    /**
     * {@inheritDoc}
     */
    public function loadProductPrices()
    {
        $products = $this->getRepository()->findAll();
        foreach ($products as $product) {
            $productRef = rand(0, 145);
            $skip       = ($productRef % 2 == 0) ? true : false;

            if (!$skip) {
                $this->createProductPrice($product);
            }
            continue;
        }
        $this->manager->flush();
    }

    /**
     * Create new product price
     * @param $product
     */
    private function createProductPrice($product)
    {
        if (!count($product->getPrices()) > 0) {
            $channels = $product->getChannels();
            $max = count($channels);
            $channelCount = rand(1, $max);
            $i=1;
            foreach ($channels as $channel) {
                if ($i <= $channelCount) {
                    $productPrice = new ProductPrice();
                    $data = $product->getData();
                    $data[PricingAwareInterface::CHANNEL_PRICING_STATE_KEY] = true;
                    $product->setData($data);
                    $productPrice->setProduct($product);
                    $productPrice->setCurrency($this->currency);
                    $productPercentage = rand(85, 95);
                    $productPriceValue = ($product->getPrice() * ($productPercentage / 100));
                    $productPrice->setValue(round((float)$productPriceValue, 2));
                    $productPrice->setChannel($channel);
                    $this->manager->persist($product);
                    $this->manager->persist($productPrice);
                }
                $i++;
            }
        }
    }

    /**
     * get repository
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function getRepository()
    {
        return $this->manager->getRepository('MarelloProductBundle:Product');
    }
}
