<?php

namespace Marello\Bundle\SubscriptionBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\PricingBundle\Entity\PriceType;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\PricingBundle\Model\PriceTypeInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadPaymentTermData;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadSubscriptionAttributeFamilyData;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadSubscriptionDurationData;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadSubscriptionSpecialPriceDurationData;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

class LoadSubscriptionProductData extends AbstractFixture implements DependentFixtureInterface
{
    const SUBSCRIPTION_PRODUCT_1_REF = 'subscription_product1';
    const SUBSCRIPTION_PRODUCT_2_REF = 'subscription_product2';
    const SUBSCRIPTION_PRODUCT_3_REF = 'subscription_product3';
    const SUBSCRIPTION_PRODUCT_4_REF = 'subscription_product4';
    const SUBSCRIPTION_PRODUCT_5_REF = 'subscription_product5';
    const SUBSCRIPTION_PRODUCT_6_REF = 'subscription_product6';

    const PRICE_REF_SUFFIX = '-price';

    /** @var \Oro\Bundle\OrganizationBundle\Entity\Organization $defaultOrganization  */
    protected $defaultOrganization;

    /** @var ObjectManager $manager */
    protected $manager;

    /** @var array $data */
    protected $data = [
        self::SUBSCRIPTION_PRODUCT_1_REF => [
            'name'          => 'subscription product 1',
            'sku'           => self::SUBSCRIPTION_PRODUCT_1_REF,
            'price'         => 10,
            'weight'        => 1.00,
            'status'        => 'enabled',
            'channel'       => 'channel1',
            'supplier'      => [
                [
                    'ref' => 'supplier1',
                    'qou' => 1,
                    'cost' => 2.50,
                    'canDropship' => false,
                    'priority' => 1
                ]
            ],
            'number_of_deliveries' => 1,
            'subscriptionDuration' => [
                'id' => LoadSubscriptionDurationData::DURATION_1_MONTH,
                'class' => LoadSubscriptionDurationData::ENUM_CLASS
            ],
            'paymentTerm' => [
                'id' => LoadPaymentTermData::DURATION_1_MONTH,
                'class' => LoadPaymentTermData::ENUM_CLASS
            ],
            'specialPriceDuration' => [
                'id' => LoadSubscriptionSpecialPriceDurationData::EQUAL_TO_SUBSCRIPTION_DURATION,
                'class' => LoadSubscriptionSpecialPriceDurationData::ENUM_CLASS
            ],
        ],
        self::SUBSCRIPTION_PRODUCT_2_REF => [
            'name'          => 'subscription product 2',
            'sku'           => self::SUBSCRIPTION_PRODUCT_2_REF,
            'price'         => 25,
            'weight'        => 2.00,
            'status'        => 'disabled',
            'channel'       => 'channel1;channel2',
            'supplier'      => [
                [
                    'ref' => 'supplier1',
                    'qou' => 1,
                    'cost' => 25.00,
                    'canDropship' => false,
                    'priority' => 2
                ],
                [
                    'ref' => 'supplier2',
                    'qou' => 1,
                    'cost' => 20.00,
                    'canDropship' => false,
                    'priority' => 1
                ]
            ],
            'number_of_deliveries' => 1,
            'subscriptionDuration' => [
                'id' => LoadSubscriptionDurationData::DURATION_3_MONTHS,
                'class' => LoadSubscriptionDurationData::ENUM_CLASS
            ],
            'paymentTerm' => [
                'id' => LoadPaymentTermData::DURATION_3_MONTHS,
                'class' => LoadPaymentTermData::ENUM_CLASS
            ],
            'specialPriceDuration' => [
                'id' => LoadSubscriptionSpecialPriceDurationData::EQUAL_TO_SUBSCRIPTION_DURATION,
                'class' => LoadSubscriptionSpecialPriceDurationData::ENUM_CLASS
            ],
        ],
        self::SUBSCRIPTION_PRODUCT_3_REF => [
            'name'          => 'subscription product 3',
            'sku'           => self::SUBSCRIPTION_PRODUCT_3_REF,
            'price'         => 50,
            'weight'        => 5.00,
            'status'        => 'disabled',
            'channel'       => 'channel1;channel3',
            'supplier'      => [
                [
                    'ref' => 'supplier1',
                    'qou' => 10,
                    'cost' => 450.00,
                    'canDropship' => false,
                    'priority' => 1
                ],
                [
                    'ref' => 'supplier1',
                    'qou' => 50,
                    'cost' => 2000.00,
                    'canDropship' => false,
                    'priority' => 1
                ]
            ],
            'number_of_deliveries' => 1,
            'subscriptionDuration' => [
                'id' => LoadSubscriptionDurationData::DURATION_6_MONTHS,
                'class' => LoadSubscriptionDurationData::ENUM_CLASS
            ],
            'paymentTerm' => [
                'id' => LoadPaymentTermData::DURATION_6_MONTHS,
                'class' => LoadPaymentTermData::ENUM_CLASS
            ],
            'specialPriceDuration' => [
                'id' => LoadSubscriptionSpecialPriceDurationData::EQUAL_TO_SUBSCRIPTION_DURATION,
                'class' => LoadSubscriptionSpecialPriceDurationData::ENUM_CLASS
            ],
        ],
        self::SUBSCRIPTION_PRODUCT_4_REF => [
            'name'          => 'subscription product 4',
            'sku'           => self::SUBSCRIPTION_PRODUCT_4_REF,
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
            ],
            'number_of_deliveries' => 1,
            'subscriptionDuration' => [
                'id' => LoadSubscriptionDurationData::DURATION_12_MONTHS,
                'class' => LoadSubscriptionDurationData::ENUM_CLASS
            ],
            'paymentTerm' => [
                'id' => LoadPaymentTermData::DURATION_12_MONTHS,
                'class' => LoadPaymentTermData::ENUM_CLASS
            ],
            'specialPriceDuration' => [
                'id' => LoadSubscriptionSpecialPriceDurationData::EQUAL_TO_SUBSCRIPTION_DURATION,
                'class' => LoadSubscriptionSpecialPriceDurationData::ENUM_CLASS
            ],
        ],
        self::SUBSCRIPTION_PRODUCT_5_REF => [
            'name'          => 'subscription product 5',
            'sku'           => self::SUBSCRIPTION_PRODUCT_5_REF,
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
            ],
            'number_of_deliveries' => 1,
            'subscriptionDuration' => [
                'id' => LoadSubscriptionDurationData::DURATION_12_MONTHS,
                'class' => LoadSubscriptionDurationData::ENUM_CLASS
            ],
            'paymentTerm' => [
                'id' => LoadPaymentTermData::DURATION_12_MONTHS,
                'class' => LoadPaymentTermData::ENUM_CLASS
            ],
            'specialPriceDuration' => [
                'id' => LoadSubscriptionSpecialPriceDurationData::EQUAL_TO_SUBSCRIPTION_DURATION,
                'class' => LoadSubscriptionSpecialPriceDurationData::ENUM_CLASS
            ],
        ],
        self::SUBSCRIPTION_PRODUCT_6_REF => [
            'name'          => 'subscription product 6',
            'sku'           => self::SUBSCRIPTION_PRODUCT_6_REF,
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
            ],
            'number_of_deliveries' => 1,
            'subscriptionDuration' => [
                'id' => LoadSubscriptionDurationData::DURATION_24_MONTHS,
                'class' => LoadSubscriptionDurationData::ENUM_CLASS
            ],
            'paymentTerm' => [
                'id' => LoadPaymentTermData::DURATION_12_MONTHS,
                'class' => LoadPaymentTermData::ENUM_CLASS
            ],
            'specialPriceDuration' => [
                'id' => LoadSubscriptionSpecialPriceDurationData::EQUAL_TO_SUBSCRIPTION_DURATION,
                'class' => LoadSubscriptionSpecialPriceDurationData::ENUM_CLASS
            ],
        ],
    ];

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
        $name = new LocalizedFallbackValue();
        $name->setString($data['name']);

        $product = new Product();
        $product
            ->setType('subscription')
            ->setSku($data['sku'])
            ->addName($name)
            ->setOrganization($this->defaultOrganization)
            ->setWeight($data['weight'])
            ->setSubscriptionDuration($this->getEnumValue(
                $data['subscriptionDuration'],
                LoadSubscriptionDurationData::ENUM_CLASS
            ))
            ->setPaymentTerm($this->getEnumValue(
                $data['paymentTerm'],
                LoadPaymentTermData::ENUM_CLASS
            ))
            ->setSpecialPriceDuration($this->getEnumValue(
                $data['specialPriceDuration'],
                LoadSubscriptionSpecialPriceDurationData::ENUM_CLASS
            ));

        $status = $this->manager
            ->getRepository('MarelloProductBundle:ProductStatus')
            ->findOneByName($data['status']);
        $product->setStatus($status);

        $subscriptionAttributeFamily = $this->manager
            ->getRepository(AttributeFamily::class)
            ->findOneBy(['code' => LoadSubscriptionAttributeFamilyData::SUBSCRIPTION_FAMILY_CODE]);
        $product->setAttributeFamily($subscriptionAttributeFamily);

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

    /**
     * @param string $id
     * @param string $class
     * @return AbstractEnumValue
     */
    private function getEnumValue($id, $class)
    {
        $className = ExtendHelper::buildEnumValueClassName($class);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $this->manager->getRepository($className);

        return $enumRepo->findOneBy(['id' => $id]);
    }
}
