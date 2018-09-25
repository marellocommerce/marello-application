<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\PricingBundle\Entity\PriceType;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Model\PriceTypeInterface;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class LoadProductChannelPricingData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadSalesData::class,
            LoadProductData::class,
        ];
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
        $handle = fopen($this->getDictionary('product_channel_prices.csv'), "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));

                $this->createProductPrice($data);
                $i++;
            }
            fclose($handle);
        }
        $this->manager->flush();
    }

    /**
     * Create new product price
     *
     * @param $data
     */
    private function createProductPrice($data)
    {
        $productResult = $this->getRepository()->findBySku($data['sku']);
        if (is_array($productResult)) {
            $product = array_shift($productResult);
        } else {
            return;
        }

        /** @var SalesChannel $channel */
        $channel                = $this->getReference($data['channel']);
        $productChannelPrice    = new ProductChannelPrice();
        $productChannelPrice->setType(
            $this->manager->getRepository(PriceType::class)->find(PriceTypeInterface::DEFAULT_PRICE)
        );
        $productData            = $product->getData();

        $productData[PricingAwareInterface::CHANNEL_PRICING_STATE_KEY] = true;
        $product->setData($productData);
        $productChannelPrice->setProduct($product);
        $productChannelPrice->setCurrency($channel->getCurrency());
        $productChannelPrice->setValue((float)$data['price']);
        $productChannelPrice->setChannel($channel);
        $this->manager->persist($product);
        $this->manager->persist($productChannelPrice);
    }

    /**
     * get repository
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function getRepository()
    {
        return $this->manager->getRepository('MarelloProductBundle:Product');
    }

    /**
     * Get dictionary file by name
     *
     * @param $name
     *
     * @return string
     */
    protected function getDictionary($name)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
    }
}
