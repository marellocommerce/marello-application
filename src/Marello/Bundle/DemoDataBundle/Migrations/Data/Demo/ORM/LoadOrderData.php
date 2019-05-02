<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

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
     * @var Warehouse
     */
    protected $defaultWarehouse;

    /**
     * @var int
     */
    protected $customers = 0;

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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->defaultWarehouse = $manager->getRepository(Warehouse::class)->getDefault();
        $this->setReference('marello_warehouse_default', $this->defaultWarehouse);

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
                    $grandTotal += $item->getRowTotalInclTax();
                });

                $grandTotal += $order->getShippingAmountInclTax();

                $order
                    ->setSubtotal($total)
                    ->setTotalTax($tax)
                    ->setGrandTotal($grandTotal)
                ;

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

        if ($order) {
            $manager->persist($order);
            $manager->flush();
        }

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
     * @param array        $row
     * @param Organization $organization
     *
     * @return Order
     */
    protected function createOrder($row, Organization $organization)
    {
        $billingAddress = new MarelloAddress();
        $billingAddress->setNamePrefix($row['title']);
        $billingAddress->setFirstName($row['firstname']);
        $billingAddress->setLastName($row['lastname']);
        $billingAddress->setStreet($row['street_address']);
        $billingAddress->setPostalCode($row['zipcode']);
        $billingAddress->setCity($row['city']);
        $billingAddress->setCountry(
            $this->manager
                ->getRepository('OroAddressBundle:Country')->find($row['country'])
        );
        $billingAddress->setRegion(
            $this->manager
                ->getRepository('OroAddressBundle:Region')
                ->findOneBy(['combinedCode' => $row['country'] . '-' . $row['state']])
        );
        $billingAddress->setPhone($row['telephone_number']);
        $billingAddress->setCompany($row['company']);
        $this->manager->persist($billingAddress);

        $shippingAddress = clone $billingAddress;
        $this->manager->persist($shippingAddress);

        $orderEntity = new Order($billingAddress, $shippingAddress);
        $customer = Customer::create($row['firstname'], $row['lastname'], $row['email'], $billingAddress);
        $customer->setOrganization($organization);
        $this->setReference('marello_customer_' . $this->customers++, $customer);
        $orderEntity->setCustomer($customer);

        /** @var SalesChannel $channel */
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
        $orderEntity->setShippingAmountExclTax($row['shipping_amount']);
        $orderEntity->setShippingAmountInclTax($row['shipping_amount']);

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
        $itemEntity->setOriginalPriceInclTax($row['price']);
        $itemEntity->setOriginalPriceExclTax($row['price']);
        $itemEntity->setPurchasePriceIncl($row['price']);
        $itemEntity->setRowTotalInclTax($row['total_price']);
        $itemEntity->setRowTotalExclTax($row['total_price']);
        $itemEntity->setTax($row['tax']);

        return $itemEntity;
    }
}
