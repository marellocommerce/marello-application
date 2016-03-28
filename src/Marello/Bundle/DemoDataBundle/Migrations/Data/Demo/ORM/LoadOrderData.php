<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\AddressBundle\Entity\Address;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LoadOrderData extends AbstractFixture implements DependentFixtureInterface
{
    /** flush manager count */
    const FLUSH_MAX = 25;

    /** @var ObjectManager $manager */
    protected $manager;

    protected $ordersFile = null;
    protected $ordersFileHeader = [];
    protected $itemsFile = null;
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        /** @var Order $order */
        $order = null;

        $createdOrders = 0;

        $organization = $manager
            ->getRepository(Organization::class)
            ->getFirst();

        while (($itemRow = $this->popOrderItemRow()) !== false) {
            if ($order && ($itemRow['order_number'] !== $order->getOrderNumber())) {
                /*
                 * Compute Order totals.
                 */
                $total = $tax = $grandTotal = 0;
                $order->getItems()->map(function (OrderItem $item) use (&$total, &$tax, &$grandTotal) {
                    $total += ($item->getQuantity() * $item->getPrice());
                    $tax += $item->getTax();
                    $grandTotal += $item->getTotalPrice();
                });

                $grandTotal += $order->getShippingAmount();

                $order
                    ->setSubtotal($total)
                    ->setTotalTax($tax)
                    ->setGrandTotal($grandTotal);

                $manager->persist($order);
                $this->setReference('marello_order_' . $createdOrders, $order);
                $createdOrders++;

                if (!($createdOrders % self::FLUSH_MAX)) {
                    $manager->flush();
                }

                $order = null;
            }

            if (!$order) {
                $orderRow = $this->popOrderRow();

                $order = $this->createOrder($orderRow, $organization);
                $order->setOrderNumber($itemRow['order_number']);
            }

            $item = $this->createOrderItem($itemRow);
            $order->addItem($item);
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
            $this->ordersFileHeader = fgetcsv($this->ordersFile, 1000, ';');
        }

        $row = fgetcsv($this->ordersFile, 1000, ';');

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
            $this->itemsFileHeader = fgetcsv($this->itemsFile, 1000, ',');
        }

        $row = fgetcsv($this->itemsFile, 1000, ',');

        return $row !== false
            ? array_combine($this->itemsFileHeader, $row)
            : false;
    }

    /**
     * @param array $row
     *
     * @return Order
     */
    protected function createOrder($row, Organization $organization)
    {
        $billing = new Address();
        $billing->setNamePrefix($row['title']);
        $billing->setFirstName($row['firstname']);
        $billing->setLastName($row['lastname']);
        $billing->setStreet($row['street_address']);
        $billing->setPostalCode($row['zipcode']);
        $billing->setCity($row['city']);
        $billing->setCountry(
            $this->manager
                ->getRepository('OroAddressBundle:Country')->find($row['country'])
        );
        $billing->setRegion(
            $this->manager
                ->getRepository('OroAddressBundle:Region')
                ->findOneBy(['combinedCode' => $row['country'] . '-' . $row['state']])
        );
        $billing->setPhone($row['telephone_number']);
        $billing->setEmail($row['email']);

        $shipping = clone $billing;

        $orderEntity = new Order($billing, $shipping);

        $channel = $this->getReference('marello_sales_channel_' . $row['channel']);
        $orderEntity->setSalesChannel($channel);
        $orderEntity->setCurrency($channel->getCurrency());

        if ($row['order_ref'] !== 'NULL') {
            $orderEntity->setOrderReference($row['order_ref']);
        }

        $orderEntity->setPaymentMethod($row['payment_method']);
        if ($row['payment_details'] !== 'NULL') {
            $orderEntity->setPaymentDetails($row['payment_details']);
        }

        $orderEntity->setShippingMethod($row['shipping_method']);
        $orderEntity->setShippingAmount($row['shipping_amount']);

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
        $itemEntity->setTotalPrice($row['total_price']);
        $itemEntity->setTax($row['tax']);

        return $itemEntity;
    }
}
