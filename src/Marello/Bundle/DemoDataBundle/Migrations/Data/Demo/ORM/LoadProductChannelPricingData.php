<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\PricingBundle\Entity\PriceType;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Model\PriceTypeInterface;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadProductChannelPricingData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadProductPriceData::class
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

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));
                $this->createProductChannelPrice($data);
            }
            fclose($handle);
        }
        $this->manager->flush();
    }

    /**
     * Create new product channel prices
     *
     * @param $data
     */
    private function createProductChannelPrice($data)
    {
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->container->get('oro_security.acl_helper');
        /** @var Product $product */
        $product = $this->manager->getRepository(Product::class)->findOneBySku($data['sku'], $aclHelper);
        if (!$product) {
            return;
        }

        /** @var SalesChannel $channel */
        $channel = $this->getReference($data['channel']);
        $prices = [];
        foreach ($data as $identifier => $priceValue) {
            if ($identifier === 'sku' || $identifier === 'channel') {
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

            $productChannelPrice = new ProductChannelPrice();
            $productChannelPrice
                ->setCurrency($channel->getCurrency())
                ->setType($type)
                ->setValue($priceValue)
                ->setProduct($product);

            $prices[$priceType] = $productChannelPrice;
        }

        $assembledChannelPriceList = $this->createAssembledChannelPricelist($channel, $prices);
        $assembledChannelPriceList->setProduct($product);
        $product->addChannelPrice($assembledChannelPriceList);

        $productData = $product->getData();
        $productData[PricingAwareInterface::CHANNEL_PRICING_STATE_KEY] = true;
        $product->setData($productData);

        $this->manager->persist($product);
    }

    /**
     * Create assembled prices list per currency and add all price types that are available
     * @param SalesChannel $channel
     * @param $prices
     * @return AssembledChannelPriceList
     */
    private function createAssembledChannelPricelist(SalesChannel $channel, $prices)
    {
        $assembledChannelPriceList = new AssembledChannelPriceList();
        $assembledChannelPriceList
            ->setCurrency($channel->getCurrency())
            ->setChannel($channel);

        /** @var ProductChannelPrice $price */
        foreach ($prices as $type => $price) {
            $method = sprintf('set%sPrice', ucfirst($type));
            $assembledChannelPriceList->$method($price);
        }

        return $assembledChannelPriceList;
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
