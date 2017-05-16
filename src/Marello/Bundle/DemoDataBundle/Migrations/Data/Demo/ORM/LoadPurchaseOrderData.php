<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadPurchaseOrderData extends AbstractFixture implements DependentFixtureInterface
{

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
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $organization = $manager->getRepository(Organization::class)->getFirst();

        $handle = fopen(__DIR__ . '/dictionaries/purchase_orders.csv', 'r');

        $replenishmentClass = ExtendHelper::buildEnumValueClassName('marello_product_reple');
        $replenishmentNOS = $manager->getRepository($replenishmentClass)->find('never_out_of_stock');

        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $headers = $data;
            }
            $orders  = [];
            $orderNo = 1;
            $order   = null;

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));

                if ($order !== null && $data['po'] !== $orderNo) {
                    $this->setReference('marello-purchase-order-' . $orderNo, $order);
                    $manager->persist($order);
                }

                if (!array_key_exists($data['po'], $orders)) {
                    $order   = $orders[$data['po']] = new PurchaseOrder($organization);
                    $orderNo = $data['po'];
                }

                /** @var Product $product */
                $product = $manager->getRepository(Product::class)
                    ->findOneBy(['sku' => $data['product']]);

                //set replenishment to product to make sure it follows PO logic
                $product->setReplenishment($replenishmentNOS);

                $purchaseOrderItem = new PurchaseOrderItem();
                $order->addItem(
                    $purchaseOrderItem->setProduct($product)->setOrderedAmount($data['ordered_amount'])
                );

                if (!$order->getSupplier()) {
                    $order->setSupplier($order->getItems()->first()->getProduct()->getPreferredSupplier());
                }
            }
            $this->setReference('marello-purchase-order-' . $orderNo, $order);
            $manager->persist($product);
            $manager->persist($order);

            $manager->flush();

            fclose($handle);
        }
    }
}
