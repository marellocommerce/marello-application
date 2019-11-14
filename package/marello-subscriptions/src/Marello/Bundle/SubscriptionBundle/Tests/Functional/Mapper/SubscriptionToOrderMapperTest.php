<?php

namespace Marello\Bundle\SubscriptionBundle\Tests\Functional\Mapper;

use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerData;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\SubscriptionBundle\Entity\Subscription;
use Marello\Bundle\SubscriptionBundle\Entity\SubscriptionItem;
use Marello\Bundle\SubscriptionBundle\Mapper\SubscriptionToOrderMapper;
use Marello\Bundle\SubscriptionBundle\Tests\Functional\DataFixtures\LoadSubscriptionProductData;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class SubscriptionToOrderMapperTest extends WebTestCase
{
    const PAYMENT_METHOD = 'default_payment_method';
    const SHIPPING_METHOD = 'manual_shipping';
    const SHIPPING_METHOD_TYPE = 'primary';

    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadSubscriptionProductData::class,
            LoadCustomerData::class,
            LoadSalesData::class
        ]);
    }

    public function testMap()
    {
        /** @var SubscriptionToOrderMapper $subscriptionToOrderMapper */
        $subscriptionToOrderMapper = $this->getContainer()->get('marello_subscription.mapper.subscription_to_order');
        /** @var Customer $customer */
        $customer = $this->getReference('marello-customer-1');
        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->getReference(LoadSalesData::CHANNEL_1_REF);
        /** @var Organization[] $organizations */
        $organizations = $this->getContainer()->get('doctrine')
            ->getManagerForClass(Organization::class)
            ->getRepository(Organization::class)
            ->findAll();
        $organization = reset($organizations);
        
        /** @var Product $product */
        $product = $this->getReference(LoadSubscriptionProductData::SUBSCRIPTION_PRODUCT_1_REF);
        
        $subscriptionItem = new SubscriptionItem();
        $subscriptionItem
            ->setSku($product->getSku())
            ->setPrice(10)
            ->setDuration($product->getSubscriptionDuration())
            ->setSpecialPrice(8)
            ->setSpecialPriceDuration($product->getSpecialPriceDuration());

        $duration = $product->getSubscriptionDuration()->getId();
        $startDate = \DateTime::createFromFormat('d/m/Y', '01/02/2019');
        $terminationDate = clone $startDate;
        $terminationDate = $terminationDate->modify(sprintf('+%d month', (int)$duration));

        $subscription = new Subscription();
        $subscription
            ->setDuration($subscriptionItem->getDuration())
            ->setStartDate($startDate)
            ->setTerminationDate($terminationDate)
            ->setBillingAddress($customer->getPrimaryAddress())
            ->setShippingAddress($customer->getPrimaryAddress())
            ->setPaymentMethod(self::PAYMENT_METHOD)
            ->setPaymentFreq($product->getPaymentTerm()->getId())
            ->setSalesChannel($salesChannel)
            ->setShippingMethod(self::SHIPPING_METHOD)
            ->setShippingMethodType(self::SHIPPING_METHOD_TYPE)
            ->setCurrency($salesChannel->getCurrency())
            ->setCustomer($customer)
            ->setItem($subscriptionItem)
            ->setOrganization($organization);
        $expectedOrder = $this->createOrder($product, $customer, $salesChannel, $organization);
        $actualOrder = $subscriptionToOrderMapper->map($subscription);

        static::assertEquals($expectedOrder, $actualOrder);
    }

    /**
     * @param Product $product
     * @param Customer $customer
     * @param SalesChannel $salesChannel
     * @param Organization $organization
     * @return Order
     */
    private function createOrder(
        Product $product,
        Customer $customer,
        SalesChannel $salesChannel,
        Organization $organization
    ) {
         $orderItem = new OrderItem();
        $orderItem
            ->setProduct($product)
            ->setQuantity(1)
            ->setPrice(8);

        $order = new Order();
        $order
            ->setBillingAddress($customer->getPrimaryAddress())
            ->setShippingAddress($customer->getPrimaryAddress())
            ->setPaymentMethod(self::PAYMENT_METHOD)
            ->setSalesChannel($salesChannel)
            ->setShippingMethod(self::SHIPPING_METHOD)
            ->setShippingMethodType(self::SHIPPING_METHOD_TYPE)
            ->setCurrency($salesChannel->getCurrency())
            ->setCustomer($customer)
            ->addItem($orderItem)
            ->setOrganization($organization);

        return $order;
    }
}
