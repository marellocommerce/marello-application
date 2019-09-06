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
use Marello\Bundle\SubscriptionBundle\Mapper\OrderToSubscriptionsMapper;
use Marello\Bundle\SubscriptionBundle\Tests\Functional\DataFixtures\LoadSubscriptionProductData;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class OrderToSubscriptionsMapperTest extends WebTestCase
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
        /** @var OrderToSubscriptionsMapper $orderToSubscriptionsMapper */
        $orderToSubscriptionsMapper = $this->getContainer()->get('marello_subscription.mapper.order_to_subscriptions');

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
        
        $order = new Order($customer->getPrimaryAddress(), $customer->getPrimaryAddress());
        $product1 = $this->getReference(LoadSubscriptionProductData::SUBSCRIPTION_PRODUCT_1_REF);
        $orderItem1 = new OrderItem();
        $orderItem1->setProduct($product1);
        $product2 = $this->getReference(LoadSubscriptionProductData::SUBSCRIPTION_PRODUCT_2_REF);
        $orderItem2 = new OrderItem();
        $orderItem2->setProduct($product2);
        $order
            ->setCustomer($customer)
            ->setCurrency($salesChannel->getCurrency())
            ->setPaymentMethod(self::PAYMENT_METHOD)
            ->setShippingMethod(self::SHIPPING_METHOD)
            ->setShippingMethodType(self::SHIPPING_METHOD_TYPE)
            ->setPurchaseDate(\DateTime::createFromFormat('d/m/Y', '15/01/2019'))
            ->setSalesChannel($salesChannel)
            ->addItem($orderItem1)
            ->addItem($orderItem2)
            ->setOrganization($organization);
        
        $expectedSubscriptions = [
            $this->createSubscription($product1, $customer, $salesChannel, $organization),
            $this->createSubscription($product2, $customer, $salesChannel, $organization),
        ];
        
        $actualSubscriptions = $orderToSubscriptionsMapper->map($order);
        
        static::assertEquals($expectedSubscriptions, $actualSubscriptions);
    }

    /**
     * @param Product $product
     * @param Customer $customer
     * @param SalesChannel $salesChannel
     * @param Organization $organization
     * @return Subscription
     */
    private function createSubscription(
        Product $product,
        Customer $customer,
        SalesChannel $salesChannel,
        Organization $organization
    ) {
        $assembledPrice = $product->getSalesChannelPrice($salesChannel) ? :
            $product->getPrice($salesChannel->getCurrency());
        $defaultPrice = $assembledPrice->getDefaultPrice()->getValue();
        $specialPrice = $assembledPrice->getSpecialPrice() ? $assembledPrice->getSpecialPrice()->getValue() : null;

        $subscriptionItem = new SubscriptionItem();
        $subscriptionItem
            ->setSku($product->getSku())
            ->setPrice($defaultPrice)
            ->setDuration($product->getSubscriptionDuration())
            ->setSpecialPrice($specialPrice)
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

        return $subscription;
    }
}
