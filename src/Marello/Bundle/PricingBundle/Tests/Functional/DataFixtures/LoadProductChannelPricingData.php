<?php

namespace Marello\Bundle\PricingBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList;
use Marello\Bundle\PricingBundle\Entity\PriceType;
use Marello\Bundle\PricingBundle\Model\PriceTypeInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Model\PricingAwareInterface;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

class LoadProductChannelPricingData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var array $data */
    protected $data = [
        [
            'product'       => LoadProductData::PRODUCT_1_REF,
            'price'         => 9,
            'channel'       => LoadSalesData::CHANNEL_1_REF,
        ],
        [
            'product'       => LoadProductData::PRODUCT_2_REF,
            'price'         => 16,
            'channel'       => LoadSalesData::CHANNEL_1_REF,
        ],
        [
            'product'       => LoadProductData::PRODUCT_3_REF,
            'price'         => 45,
            'channel'       => LoadSalesData::CHANNEL_1_REF,
        ],
        [
            'product'       => LoadProductData::PRODUCT_4_REF,
            'price'         => 90,
            'channel'       => LoadSalesData::CHANNEL_1_REF,
        ],
        [
            'product'       => LoadProductData::PRODUCT_4_REF,
            'price'         => 80,
            'channel'       => LoadSalesData::CHANNEL_2_REF,
        ],
    ];

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
        foreach ($this->data as $data) {
            $this->createProductPrice($data);
        }
        $this->manager->flush();
    }

    /**
     * Create new product price
     *
     * @param $data
     */
    private function createProductPrice(array $data)
    {
        /** @var Product $product */
        $product = $this->getReference($data['product']);
        /** @var SalesChannel $channel */
        $channel = $this->getReference($data['channel']);
        $productChannelPrice = new ProductChannelPrice();
        $productData = $product->getData();
        $type = $this->manager->getRepository(PriceType::class)->find(PriceTypeInterface::DEFAULT_PRICE);

        $productData[PricingAwareInterface::CHANNEL_PRICING_STATE_KEY] = true;
        $product->setData($productData);
        $productChannelPrice
            ->setProduct($product)
            ->setCurrency($channel->getCurrency())
            ->setValue((float)$data['price'])
            ->setChannel($channel)
            ->setType($type);

        $assembledChannelPriceList = new AssembledChannelPriceList();
        $assembledChannelPriceList
            ->setCurrency($channel->getCurrency())
            ->setDefaultPrice($productChannelPrice)
            ->setChannel($channel)
            ->setProduct($product);

        $this->manager->persist($assembledChannelPriceList);
        $this->manager->persist($product);
    }
}
