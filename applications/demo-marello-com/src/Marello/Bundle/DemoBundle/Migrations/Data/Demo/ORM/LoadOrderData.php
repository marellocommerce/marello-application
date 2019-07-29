<?php

namespace Marello\Bundle\DemoBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSalesData;
use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData;
use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductChannelPricingData;
use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductInventoryData;
use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadCustomerData;

class LoadOrderData extends AbstractFixture implements DependentFixtureInterface
{
    /** flush manager count */
    const FLUSH_MAX = 25;

    /**
     * @var ObjectManager
     */
    protected $manager;
    
    /**
     * @var resource|null
     */
    protected $ordersFile = null;

    /**
     * @var array
     */
    protected $ordersFileHeader = [];

    /**
     * @var resource|null
     */
    protected $itemsFile = null;

    /**
     * @var array
     */
    protected $itemsFileHeader = [];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadSalesData::class,
            LoadProductData::class,
            LoadProductChannelPricingData::class,
            LoadProductInventoryData::class,
            LoadCustomerData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        return;
        $this->manager = $manager;
        $organization = $manager
            ->getRepository(Organization::class)
            ->getFirst();
        $order = null;
        while (($itemRow = $this->popOrderItemRow()) !== false) {
            if ($order && ($itemRow['order_number'] !== $order->getOrderNumber())) {
                $order = null;
            }

            if (!$order) {
                $orderRow = $this->popOrderRow();
                /** @var Order $order */
                $order = $this->createOrder($orderRow, $organization);
                $order->setOrderNumber($itemRow['order_number']);
            }

            $item = $this->createOrderItem($itemRow);
            if ($item) {
                $order->addItem($item);
                $this->setReference(sprintf('marello-order-%s', $order->getOrderNumber()), $order);
            }

            $manager->persist($order);
        }

        $manager->flush();
        $this->closeFiles();
    }

    /**
     * Close all open files.
     */
    protected function closeFiles()
    {
        if ($this->ordersFile) {
            fclose($this->ordersFile);
        }

        if ($this->itemsFile) {
            fclose($this->itemsFile);
        }
    }

    /**
     * @return array|bool
     */
    protected function popOrderRow()
    {
        if (!$this->ordersFile) {
            $this->ordersFile       = fopen(__DIR__ . '/dictionaries/order_data.csv', 'r');
            $this->ordersFileHeader = fgetcsv($this->ordersFile, 2000, ',');
        }

        $row = fgetcsv($this->ordersFile, 2000, ',');

        return $row !== false
            ? array_combine($this->ordersFileHeader, $row)
            : false;
    }

    /**
     * @return array|bool
     */
    protected function popOrderItemRow()
    {
        if (!$this->itemsFile) {
            $this->itemsFile       = fopen(__DIR__ . '/dictionaries/order_items.csv', 'r');
            $this->itemsFileHeader = fgetcsv($this->itemsFile, 2000, ',');
        }

        $row = fgetcsv($this->itemsFile, 2000, ',');

        return $row !== false
            ? array_combine($this->itemsFileHeader, $row)
            : false;
    }

    /**
     * @param array        $row
     * @param Organization $organization
     *
     * @return Order
     */
    protected function createOrder($row, Organization $organization)
    {
        if (!isset($row['customer_reference_number'])) {
            return;
        }
        /** @var Customer $customer */
        $customer = $this->getReference(sprintf('marello-customer-%s', $row['customer_reference_number']));

        /** @var Order $orderEntity */
        $orderEntity = new Order($customer->getPrimaryAddress(), $customer->getShippingAddress());
        $orderEntity->setCustomer($customer);

        /** @var SalesChannel $channel */
        $channel = $this->getReference($row['saleschannel']);
        $this->fixSalesChannelReference($channel);
        $orderEntity->setSalesChannel($channel);
        $orderEntity->setCurrency($channel->getCurrency());

        if (isset($row['localization']) && !empty($row['localization'])) {
            /** @var Localization $localization */
            $localization = $this->getReference(sprintf('localization_%s', $row['localization']));
            $orderEntity->setLocalization($localization);
        }

        $orderEntity
            ->setSubtotal($row['subtotal'])
            ->setTotalTax($row['total_tax'])
            ->setGrandTotal($row['grand_total'])
        ;
        if (isset($row['discount_amount']) && !empty($row['discount_amount'])) {
            $orderEntity->setDiscountAmount($row['discount_amount']);
        }

        if (isset($row['coupon_code']) && !empty($row['coupon_code'])) {
            $orderEntity->setCouponCode($row['coupon_code']);
        }

        $orderEntity->setShippingMethod($row['shipping_method']);
        $orderEntity->setShippingMethodType($row['shipping_method_type']);
        $orderEntity->setEstimatedShippingCostAmount($row['estimated_shipping_cost_amount']);
        $orderEntity->setOrganization($organization);

        return $orderEntity;
    }

    /**
     * @param array $row
     *
     * @return OrderItem
     */
    protected function createOrderItem($row)
    {
        /** @var Product $product */
        $product = $this->manager
            ->getRepository('MarelloProductBundle:Product')
            ->findOneBy(['sku' => $row['sku']]);

        $itemEntity = new OrderItem();
        $itemEntity->setProduct($product);
        $itemEntity->setQuantity($row['qty']);
        $itemEntity->setPrice($row['price']);
        $itemEntity->setPurchasePriceIncl($row['price']);
        $itemEntity->setRowTotalInclTax($row['total_price_incl_tax']);
        $itemEntity->setRowTotalExclTax($row['total_price_excl_tax']);
        $itemEntity->setTax($row['tax']);

        return $itemEntity;
    }

    /**
     * tmp fix for returns
     * @param $channel
     */
    private function fixSalesChannelReference($channel)
    {
        $this->setReference('marello_sales_channel_1', $channel);
    }
}
