<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\InitialFixtureInterface;
use Oro\Bundle\LocaleBundle\Tests\Functional\DataFixtures\LoadLocalizationData;
use Oro\Bundle\EntityConfigBundle\Tests\Functional\DataFixtures\LoadAttributeFamilyData;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\PricingBundle\Entity\PriceType;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\PricingBundle\Model\PriceTypeInterface;
use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;
use Marello\Bundle\ProductBundle\Entity\Builder\ProductFamilyBuilder;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures\LoadSupplierData;

class LoadProductData extends AbstractFixture implements DependentFixtureInterface, InitialFixtureInterface
{
    const PRODUCT_1_REF = 'product1';
    const PRODUCT_2_REF = 'product2';
    const PRODUCT_3_REF = 'product3';
    const PRODUCT_4_REF = 'product4';
    const PRODUCT_5_REF = 'product5';
    const PRODUCT_6_REF = 'product6';

    const PRICE_REF_SUFFIX = '-price';

    /** @var \Oro\Bundle\OrganizationBundle\Entity\Organization $defaultOrganization  */
    protected $defaultOrganization;

    /** @var ObjectManager $manager */
    protected $manager;

    /** @var array $data */
    protected $data = [
        self::PRODUCT_1_REF => [
            'names'          => [
                ['reference' => 'product1.names.default', 'string' => 'product1']
            ],
            'sku'           => 'p1',
            'price'         => 10,
            'weight'        => 1.00,
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
            'names'          => [
                ['reference' => 'product2.names.default', 'string' => 'product2']
            ],
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
            'names'          => [
                ['reference' => 'product3.names.default', 'string' => 'product3']
            ],
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
            'names'          => [
                ['reference' => 'product4.names.default', 'string' => 'product4']
            ],
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
        self::PRODUCT_5_REF => [
            'names'          => [
                ['reference' => 'product5.names.default', 'string' => 'product5']
            ],
            'sku'           => 'p5',
            'price'         => 10,
            'weight'        => 1.00,
            'status'        => 'enabled',
            'channel'       => 'channel1;channel2;channel3',
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
        self::PRODUCT_6_REF => [
            'names'          => [
                ['reference' => 'product6.names.default', 'string' => 'product6']
            ],
            'sku'           => 'p6',
            'price'         => 10,
            'weight'        => 1.00,
            'status'        => 'enabled',
            'channel'       => 'channel1;channel2;channel3',
            'supplier'      => [
                [
                    'ref' => 'supplier1',
                    'qou' => 1,
                    'cost' => 2.50,
                    'canDropship' => false,
                    'priority' => 1
                ]
            ]
        ],
    ];

    public function getDependencies()
    {
        return [
            LoadLocalizationData::class,
            LoadAttributeFamilyData::class,
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
        if (!empty($data['names'])) {
            foreach ($data['names'] as $name) {
                $product->addName($this->createLocalizedValue($name));
            }
        }
        $product->setOrganization($this->defaultOrganization);
        $product->setWeight($data['weight']);

        $status = $this->manager
            ->getRepository('MarelloProductBundle:ProductStatus')
            ->findOneByName($data['status']);
        $product->setStatus($status);

        $defaultAttributeFamily = $this->manager
            ->getRepository(AttributeFamily::class)
            ->findOneBy(['code' => ProductFamilyBuilder::DEFAULT_FAMILY_CODE]);
        $product->setAttributeFamily($defaultAttributeFamily);

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

            if (null === $preferredSupplier) {
                $preferredSupplier = $supplier;
                $preferredPriority = $priority;
            }
            if ($priority < $preferredPriority) {
                $preferredSupplier = $supplier;
                $preferredPriority = $priority;
            }

            $product->setPreferredSupplier($preferredSupplier);
        }
    }

    /**
     * @param array $name
     * @return LocalizedFallbackValue
     */
    protected function createLocalizedValue(array $name)
    {
        $value = new LocalizedFallbackValue();
        if (array_key_exists('localization', $name)) {
            /** @var Localization $localization */
            $localization = $this->getReference($name['localization']);
            $value->setLocalization($localization);
        }
        if (array_key_exists('fallback', $name)) {
            $value->setFallback($name['fallback']);
        }
        if (array_key_exists('string', $name)) {
            $value->setString($name['string']);
        }
        if (array_key_exists('text', $name)) {
            $value->setText($name['text']);
        }
        $this->setReference($name['reference'], $value);

        return $value;
    }
}
