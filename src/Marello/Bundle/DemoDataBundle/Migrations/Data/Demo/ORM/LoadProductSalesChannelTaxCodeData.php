<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;

class LoadProductSalesChannelTaxCodeData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /** @var array $data */
    protected $data = [
        [
            'code'      => 'sales_channel_de_munchen',
            'tax_code'  => 'DE_high'
        ],
        [
            'code'      => 'sales_channel_de_berlin',
            'tax_code'  => 'DE_high'
        ],
        [
            'code'      => 'sales_channel_de_frankfurt',
            'tax_code'  => 'DE_high'
        ],
        [
            'code'      => 'sales_channel_us_webshop',
            'tax_code'  => 'US'
        ],
        [
            'code'      => 'sales_channel_de_webshop',
            'tax_code'  => 'DE_high'
        ],
        [
            'code'      => 'sales_channel_fr_webshop',
            'tax_code'  => 'FR_high'
        ],
        [
            'code'      => 'sales_channel_uk_webshop',
            'tax_code'  => 'UK_high'
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadSalesData::class,
            LoadProductData::class,
            LoadTaxRuleData::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        /** @var Product[] $products */
        $products = $this->manager
            ->getRepository('MarelloProductBundle:Product')
            ->findAll();

        foreach ($products as $product) {
            $this->loadProductChannelTaxData($product);
        }

        $this->manager->flush();
    }

    /**
     * @param Product $product
     * load products channel tax data
     */
    protected function loadProductChannelTaxData(Product $product)
    {
        foreach ($this->data as $channelTaxCodeData) {
            /** @var SalesChannel $channel */
            $channel = $this->getReference($channelTaxCodeData['code']);
            /** @var TaxCode $taxCode */
            $taxCode = $this->getReference($channelTaxCodeData['tax_code']);

            $productSalesChannelRelation = new ProductChannelTaxRelation();
            $productSalesChannelRelation
                ->setProduct($product)
                ->setSalesChannel($channel)
                ->setTaxCode($taxCode)
            ;
            $this->manager->persist($productSalesChannelRelation);
            $product->addSalesChannelTaxCode($productSalesChannelRelation);
        }
    }
}
