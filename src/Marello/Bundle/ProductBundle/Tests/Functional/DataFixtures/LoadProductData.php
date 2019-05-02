<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\PricingBundle\Entity\PriceType;
use Marello\Bundle\PricingBundle\Model\PriceTypeInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures\LoadSupplierData;

class LoadProductData extends AbstractFixture implements DependentFixtureInterface
{
    const PRODUCT_1_REF = 'product1';
    const PRODUCT_2_REF = 'product2';
    const PRODUCT_3_REF = 'product3';
    const PRODUCT_4_REF = 'product4';

    const PRICE_REF_SUFFIX = '-price';

    /** @var \Oro\Bundle\OrganizationBundle\Entity\Organization $defaultOrganization  */
    protected $defaultOrganization;

    /** @var ObjectManager $manager */
    protected $manager;

    /** @var array $data */
    protected $data = [
        self::PRODUCT_1_REF => [
            'name'          => 'product1',
            'sku'           => 'p1',
            'price'         => 10,
            'weight'        => 0.00,
            'status'        => 'enabled',
            'channel'       => 'channel1',
            'supplier'      => [
                [
                    'ref' => 'supplier1',
                    'qou' => 1,
                    'cost' => 2.50,
                    'canDropship' => true,
                    'priority' => 1
                ]
            ]
        ],
        self::PRODUCT_2_REF => [
            'name'          => 'product 2',
            'sku'           => 'p2',
            'price'         => 25,
            'weight'        => 2.00,
            'status'        => 'disabled',
            'channel'       => 'channel1;channel2',
            'supplier'      => [
                [
                    'ref' => 'supplier1',
                    'qou' => 1,
                    'cost' => 25.00,
                    'canDropship' => true,
                    'priority' => 2
                ],
                [
                    'ref' => 'supplier2',
                    'qou' => 1,
                    'cost' => 20.00,
                    'canDropship' => false,
                    'priority' => 1
                ]
            ]
        ],
        self::PRODUCT_3_REF => [
            'name'          => 'product 3',
            'sku'           => 'p3',
            'price'         => 50,
            'weight'        => 5.00,
            'status'        => 'disabled',
            'channel'       => 'channel1;channel3',
            'supplier'      => [
                [
                    'ref' => 'supplier1',
                    'qou' => 10,
                    'cost' => 450.00,
                    'canDropship' => true,
                    'priority' => 1
                ],
                [
                    'ref' => 'supplier1',
                    'qou' => 50,
                    'cost' => 2000.00,
                    'canDropship' => true,
                    'priority' => 1
                ]
            ]
        ],
        self::PRODUCT_4_REF => [
            'name'          => 'product 4',
            'sku'           => 'p4',
            'price'         => 100,
            'weight'        => 10.00,
            'stockLevel'    => 0,
            'status'        => 'enabled',
            'channel'       => 'channel1;channel2;channel3',
            'supplier'      => [
                [
                    'ref' => 'supplier3',
                    'qou' => 1,
                    'cost' => 75.00,
                ],
                [
                    'ref' => 'supplier3',
                    'qou' => 10,
                    'cost' => 750.00,
                ]
            ]
        ],
    ];

    public function getDependencies()
    {
        return [
            LoadSalesData::class,
            LoadSupplierData::class,
            LoadTaxCodeData::class
        ];
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $organizations = $this->manager
            ->getRepository('OroOrganizationBundle:Organization')
            ->findAll();

        if (is_array($organizations) && count($organizations) > 0) {
            $this->defaultOrganization = array_shift($organizations);
        }

        $this->loadProducts();
    }

    /**
     * load products
     */
    public function loadProducts()
    {
        foreach ($this->data as $productKey => $data) {
            $product = $this->createProduct($data);
            $this->setReference($productKey, $product);
        }
        $this->manager->flush();
    }

    /**
     * create new products
     * @param array $data
     * @return Product $product
     */
    private function createProduct(array $data)
    {
        $product = new Product();
        $product->setSku($data['sku']);
        $product->setName($data['name']);
        $product->setOrganization($this->defaultOrganization);
        $product->setWeight($data['weight']);

        $status = $this->manager
            ->getRepository('MarelloProductBundle:ProductStatus')
            ->findOneByName($data['status']);
        $product->setStatus($status);

        $currencies = $this->addSalesChannels($product, $data);
        $channelCurrencies = array_unique($currencies);
        $this->addDefaultPricesForCurrencies($product, $channelCurrencies, $data['price']);

        $this->addProductTaxes($product);

        $this->addProductSuppliers($product, $data);
        $this->manager->persist($product);

        return $product;
    }

    /**
     * Add sales channels to product
     * @param Product $product
     * @param array $data
     * @return array
     */
    protected function addSalesChannels(Product $product, array $data)
    {
        $channels = explode(';', $data['channel']);
        $currencies = [];
        foreach ($channels as $channelId) {
            /** @var SalesChannel $channel */
            $channel = $this->getReference($channelId);
            $product->addChannel($channel);
            $currencies[] = $channel->getCurrency();
        }

        return $currencies;
    }

    /**
     * Add default prices based on the available saleschannel currencies
     * @param Product $product
     * @param array $currencies
     * @param $defaultPrice
     */
    protected function addDefaultPricesForCurrencies(Product $product, array $currencies, $defaultPrice)
    {
        $defaultPriceType = $this->manager->getRepository(PriceType::class)->find(PriceTypeInterface::DEFAULT_PRICE);

        /**
         * add default prices for all currencies
         */
        foreach ($currencies as $currency) {
            // add prices
            $price = new ProductPrice();
            $price
                ->setType($defaultPriceType)
                ->setCurrency($currency);
            if (count($currencies) > 1 && $currency === 'USD') {
                $price->setValue(($defaultPrice * 1.12));
            } else {
                $price->setValue($defaultPrice);
            }

            $assembledPriceList = new AssembledPriceList();
            $assembledPriceList
                ->setCurrency($currency)
                ->setDefaultPrice($price);

            $product->addPrice($assembledPriceList);
        }
    }

    /**
     * Add taxcodes for product and it's sales channels
     *
     * @param Product $product
     */
    protected function addProductTaxes(Product $product)
    {
        $product->setTaxCode($this->getReference(LoadTaxCodeData::TAXCODE_2_REF));
        foreach ($product->getChannels() as $channel) {
            $productChannelTaxRelation = new ProductChannelTaxRelation();
            $productChannelTaxRelation
                ->setSalesChannel($channel)
                ->setProduct($product)
                ->setTaxCode($this->getReference(LoadTaxCodeData::TAXCODE_3_REF))
            ;
            $product->addSalesChannelTaxCode($productChannelTaxRelation);
        }
    }


    /**
     * Add product suppliers to product
     * @param Product $product
     * @param array $data
     */
    protected function addProductSuppliers(Product $product, array $data)
    {
        $preferredSupplier = null;
        $preferredPriority = 0;

        foreach ($data['supplier'] as $supplierData) {
            /** @var Supplier $supplier */
            $supplier = $this->getReference($supplierData['ref']);
            $qoU = $supplierData['qou'];
            $priority = isset($supplierData['priority']) ? $supplierData['priority'] : $supplier->getPriority();
            $canDropship = isset($supplierData['canDropship']) ?
                $supplierData['canDropship'] : $supplier->getCanDropship();

            $cost = $supplierData['cost'];

            $productSupplierRelation = new ProductSupplierRelation();
            $productSupplierRelation
                ->setProduct($product)
                ->setSupplier($supplier)
                ->setQuantityOfUnit($qoU)
                ->setCanDropship($canDropship)
                ->setPriority($priority)
                ->setCost($cost)
            ;
            $this->manager->persist($productSupplierRelation);
            $product->addSupplier($productSupplierRelation);

            if (null == $preferredSupplier) {
                $preferredSupplier = $supplier;
                $preferredPriority = $priority;
                continue;
            }
            if ($priority < $preferredPriority) {
                $preferredSupplier = $supplier;
                $preferredPriority = $priority;
            }

            $product->setPreferredSupplier($preferredSupplier);
        }
    }
}
