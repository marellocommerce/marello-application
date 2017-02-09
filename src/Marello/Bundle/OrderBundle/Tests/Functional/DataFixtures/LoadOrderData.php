<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadProductChannelPricingData;

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

    /** @var Warehouse */
    protected $defaultWarehouse;

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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->defaultWarehouse = $manager->getRepository(Warehouse::class)->getDefault();
//        $this->setReference('marello_warehouse_default', $this->defaultWarehouse);

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
                $this->setReference('order' . $createdOrders, $order);
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

        $manager->persist($order);
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
     * @param array        $row
     * @param Organization $organization
     *
     * @return Order
     */
    protected function createOrder($row, Organization $organization)
    {
        $address = new MarelloAddress();
        $address->setNamePrefix($row['title']);
        $address->setFirstName($row['firstname']);
        $address->setLastName($row['lastname']);
        $address->setStreet($row['street_address']);
        $address->setPostalCode($row['zipcode']);
        $address->setCity($row['city']);
        $address->setCountry(
            $this->manager
                ->getRepository('OroAddressBundle:Country')->find($row['country'])
        );
        $address->setRegion(
            $this->manager
                ->getRepository('OroAddressBundle:Region')
                ->findOneBy(['combinedCode' => $row['country'] . '-' . $row['state']])
        );
        $address->setPhone($row['telephone_number']);
        $address->setCompany($row['company']);
        $this->manager->persist($address);

        $orderEntity = new Order($address, $address);
        $customer = Customer::create($row['firstname'], $row['lastname'], $row['email'], $address);
        $customer->setOrganization($organization);
        $this->setReference('customer' . $this->customers++, $customer);
        $orderEntity->setCustomer($customer);

        $channel = $this->getReference($row['channel']);
        $orderEntity->setSalesChannel($channel);
        $orderEntity->setCurrency($channel->getCurrency());

        if ($row['order_ref'] !== 'NULL') {
            $orderEntity->setOrderReference($row['order_ref']);
        }

        $orderEntity->setPaymentMethod($row['payment_method']);
        if ($row['payment_details'] !== 'NULL') {
            $orderEntity->setPaymentDetails($row['payment_details']);
        }

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
