<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures\LoadSupplierData;

class LoadPurchaseOrderData extends AbstractFixture implements DependentFixtureInterface
{
    const PURCHASE_ORDER_1_REF = 'purchaseOrder1';
    const PURCHASE_ORDER_2_REF = 'purchaseOrder2';

    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        self::PURCHASE_ORDER_1_REF => [
            'supplier' => LoadSupplierData::SUPPLIER_1_REF,
            'orderTotal' => 190,
            'items' => [
                0 => [
                    'product' => LoadProductData::PRODUCT_1_REF,
                    'orderedAmount' => 4,
                    'rowTotal' => 40
                ],
                1 => [
                    'product' => LoadProductData::PRODUCT_2_REF,
                    'orderedAmount' => 6,
                    'rowTotal' => 150
                ]
            ]
        ],
        self::PURCHASE_ORDER_2_REF => [
            'supplier' => LoadSupplierData::SUPPLIER_2_REF,
            'orderTotal' => 1000,
            'items' => [
                0 => [
                    'product' => LoadProductData::PRODUCT_3_REF,
                    'orderedAmount' => 20,
                    'rowTotal' => 1000
                ]
            ]
        ]
    ];

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadProductData::class,
            LoadSupplierData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadPurchaseOrders();
    }

    /**
     * load Purchase Orders
     */
    public function loadPurchaseOrders()
    {
        foreach ($this->data as $purchaseOrderKey => $data) {
            $purchaseOrder = $this->createPurchaseOrder($data);
            $this->setReference($purchaseOrderKey, $purchaseOrder);
        }
        $this->manager->flush();
    }

    /**
     * creates new purchase orders
     *
     * @param array $data
     * @return PurchaseOrder $purchaseOrder
     */
    protected function createPurchaseOrder(array $data)
    {
        $organization = $this->manager
            ->getRepository(Organization::class)
            ->getFirst();
        $purchaseOrder = new PurchaseOrder();
        $purchaseOrder
            ->setOrganization($organization)
            ->setSupplier($this->getReference($data['supplier']))
            ->setOrderTotal($data['orderTotal'])
        ;

        foreach ($data['items'] as $item) {
            $purchaseOrderItem = new PurchaseOrderItem();
            /** @var Product $product */
            $product = $this->getReference($item['product']);
            /** @var AssembledPriceList $assembledPriceList */
            $assembledPriceList = $product->getPrices()->first();
            $purchaseOrderItem
                ->setProduct($product)
                ->setOrderedAmount($item['orderedAmount'])
                ->setRowTotal($item['rowTotal'])
                ->setPurchasePrice($assembledPriceList->getDefaultPrice())
            ;

            $purchaseOrder->addItem($purchaseOrderItem);
        }

        $this->manager->persist($purchaseOrder);

        return $purchaseOrder;
    }
}
