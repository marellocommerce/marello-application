<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerData;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadProductChannelPricingData;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadOrderData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    const DEFAULT_WAREHOUSE_REF = 'marello_warehouse_default';

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
            LoadCustomerData::class,
            LoadTaxCodeData::class,
            LoadSalesData::class,
            LoadProductData::class,
            LoadProductChannelPricingData::class,
            LoadInventoryData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $aclHelper = $this->container->get('oro_security.acl_helper');
        $this->defaultWarehouse = $manager->getRepository(Warehouse::class)->getDefault($aclHelper);
        $this->setReference(self::DEFAULT_WAREHOUSE_REF, $this->defaultWarehouse);

        /** @var Order $order */
        $order = null;

        $createdOrders = 0;

        $organization = $manager
            ->getRepository(Organization::class)
            ->getFirst();
        $taxCode = $this->manager
            ->getRepository(TaxCode::class)
            ->findOneBy([]);

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
                $createdOrders++;
                $order = null;
            }

            if (!$order) {
                $orderRow = $this->popOrderRow();

                $order = $this->createOrder($orderRow, $organization);
                $order->setOrderNumber($itemRow['order_number']);
            }

            $this->setReference('marello_order_' . $createdOrders, $order);
            $item = $this->createOrderItem($itemRow, $organization, $taxCode);
            $order->addItem($item);
            $manager->flush();
        }

        $manager->persist($order);
        $manager->flush();

        $this->closeFiles();

        $this->loadReturns($manager);
    }

    private function loadReturns(ObjectManager $manager)
    {
        /**
         * Temporary moved from LoadReturnData to avoid "A new entity was found through the relationship
            "Oro\Bundle\EmailBundle\Entity\EmailUser#organization" that was not configured
            to cascade persist operations for entity: Oro." error.
         */
        $orders = $manager->getRepository('MarelloOrderBundle:Order')->findAll();
        $channel = $this->getReference(LoadSalesData::CHANNEL_1_REF);
        $reasonClass = ExtendHelper::buildEnumValueClassName('marello_return_reason');
        $reasons = $manager->getRepository($reasonClass)->findAll();

        $i = 0;
        foreach ($orders as $order) {
            if (!$this->hasReference('marello_order_unreturned')) {
                $this->setReference('marello_order_unreturned', $order);
                continue;
            }

            $return = new ReturnEntity();

            $return->setOrder($order);
            $return->setSalesChannel($channel);
            $return->setReturnReference(uniqid($order->getOrderNumber()));

            $order->getItems()->map(function (OrderItem $item) use ($return, $reasons) {
                $returnItem = new ReturnItem($item);
                $returnItem->setQuantity(rand(1, $item->getQuantity()));
                $returnItem->setReason($reasons[rand(0, count($reasons) - 1)]);
                $return->addReturnItem($returnItem);
            });

            $manager->persist($return);
            $this->setReference('return' . $i++, $return);
        }

        $manager->flush();
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

        $shippingAddress = clone $address;
        $this->manager->persist($address);
        $this->manager->persist($shippingAddress);

        $orderEntity = new Order($address, $address);
        $customer = Customer::create($row['firstname'], $row['lastname'], $row['email'], $address, $shippingAddress);
        $customer->setOrganization($organization);
        $this->setReference('customer' . $this->customers++, $customer);
        $orderEntity->setCustomer($customer);

        /** @var SalesChannel $channel */
        $channel = $this->getReference($row['channel']);
        $orderEntity->setSalesChannel($channel);
        $orderEntity->setCurrency($channel->getCurrency());

        if ($row['order_ref'] !== 'NULL') {
            $orderEntity->setOrderReference($row['order_ref']);
        }

        $orderEntity->setPaymentMethod($row['payment_method']);

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
    protected function createOrderItem($row, Organization $organization, TaxCode $taxCode)
    {
        /** @var Product $product */
        $product = $this->manager
            ->getRepository('MarelloProductBundle:Product')
            ->findOneBy(['sku' => $row['sku'], 'organization' => $organization]);

        $itemEntity = new OrderItem();
        $itemEntity->setTaxCode($taxCode);
        $itemEntity->setProduct($product);
        $itemEntity->setQuantity($row['qty']);
        $itemEntity->setPrice($row['price']);
        $itemEntity->setOriginalPriceInclTax($row['price']);
        $itemEntity->setOriginalPriceExclTax($row['price']);
        $itemEntity->setPurchasePriceIncl($row['price']);
        $itemEntity->setRowTotalInclTax($row['total_price']);
        $itemEntity->setRowTotalExclTax($row['total_price']);
        $itemEntity->setTax($row['tax']);
        $itemEntity->setTaxCode($this->getReference($row['tax_code']));
        $itemEntity->setOrganization($organization);
        return $itemEntity;
    }
}
