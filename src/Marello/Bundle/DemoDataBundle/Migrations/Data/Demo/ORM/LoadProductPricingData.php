<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
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
        return [
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSalesData',
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData',
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
        $handle = fopen($this->getDictionary('product_prices.csv'), "r");
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

        $channel      = $this->getReference('marello_sales_channel_' . (int)$data['channel']);
        $productPrice = new ProductPrice();
        $productData  = $product->getData();

        $productData[PricingAwareInterface::CHANNEL_PRICING_STATE_KEY] = true;
        $product->setData($productData);
        $productPrice->setProduct($product);
        $productPrice->setCurrency($this->currency);
        $productPrice->setValue((float)$data['price']);
        $productPrice->setChannel($channel);
        $this->manager->persist($product);
        $this->manager->persist($productPrice);
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
    private function getDictionary($name)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
    }
}
