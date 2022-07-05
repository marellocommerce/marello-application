<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\PricingBundle\Entity\PriceType;
use Marello\Bundle\PricingBundle\Model\PriceTypeInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadProductPriceData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadProductData::class
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
     * load product prices
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
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));
                $this->createProductPrice($data);
            }
            fclose($handle);
        }
        $this->manager->flush();
    }

    /**
     * create new product prices
     * @param array $data
     */
    private function createProductPrice(array $data)
    {
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->container->get('oro_security.acl_helper');
        /** @var Product $product */
        $product = $this->manager->getRepository(Product::class)->findOneBySku($data['sku'], $aclHelper);
        if (!$product) {
            return;
        }
        $currencies = $this->createPricesByCurrency($data, $product);

        foreach ($currencies as $currency => $prices) {
            $assembledPriceList = $this->createAssembledPricelist($currency, $prices);
            $assembledPriceList->setProduct($product);
            $product->addPrice($assembledPriceList);
            $this->setReference(sprintf('marello_product_price_%s', $product->getSku()), $assembledPriceList);
        }

        $this->manager->persist($product);
    }

    /**
     * Create assembled prices list per currency and add all price types that are available
     * @param $currency
     * @param $prices
     * @return AssembledPriceList
     */
    private function createAssembledPricelist($currency, $prices)
    {
        $assembledPriceList = new AssembledPriceList();
        $assembledPriceList->setCurrency($currency);

        /** @var ProductPrice $price */
        foreach ($prices as $type => $price) {
            $method = sprintf('set%sPrice', ucfirst($type));
            $assembledPriceList->$method($price);
        }

        return $assembledPriceList;
    }

    /**
     * Create prices by type and currency
     * @param array $data
     * @param Product $product
     * @return array
     */
    private function createPricesByCurrency(array $data, Product $product)
    {
        $currencies = [];
        foreach ($data as $identifier => $priceValue) {
            if ($identifier === 'sku') {
                continue;
            }

            if (!$priceValue) {
                continue;
            }

            $identifiers = explode('_', $identifier);
            $priceType = array_shift($identifiers);

            $type = $this->manager->getRepository(PriceType::class)->find($priceType);
            if (!$type) {
                $type = $this->manager->getRepository(PriceType::class)->find(PriceTypeInterface::DEFAULT_PRICE);
            }

            $currency = array_pop($identifiers);
            $price = new ProductPrice();
            $price
                ->setCurrency($currency)
                ->setType($type)
                ->setValue($priceValue)
                ->setProduct($product);

            $currencies[$currency][$priceType] = $price;
        }

        return $currencies;
    }

    /**
     * Get dictionary file by name
     * @param $name
     * @return string
     */
    protected function getDictionary($name)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
    }
}
