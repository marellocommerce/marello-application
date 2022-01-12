<?php

namespace Marello\Bundle\SubscriptionBundle\Tests\Functional\Controller;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadCustomerData;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodTypeInterface;
use Marello\Bundle\SubscriptionBundle\Entity\Subscription;
use Marello\Bundle\SubscriptionBundle\Tests\Functional\DataFixtures\LoadSubscriptionProductData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadSubscriptionProductData::class,
            LoadCustomerData::class,
            LoadSalesData::class
        ]);
    }

    public function testIndexAction()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_subscription_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testCreateAction()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_subscription_create'));
        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();

        /** @var Customer $subscriptionCustomer */
        $subscriptionCustomer = $this->getReference('marello-customer-1');

        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->getReference(LoadSalesData::CHANNEL_1_REF);

        /** @var Product $product */
        $product = $this->getReference(LoadSubscriptionProductData::SUBSCRIPTION_PRODUCT_1_REF);

        $submittedData = $this->getCreateFormData($form, $subscriptionCustomer, $salesChannel, $product);

        $this->client->followRedirects(true);

        $this->client->request($form->getMethod(), $form->getUri(), $submittedData);
        $result  = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        /** @var Subscription $subscription */
        $subscription = $this->getContainer()->get('doctrine')
            ->getManagerForClass(Subscription::class)
            ->getRepository(Subscription::class)
            ->findOneBy([
                'customer' => $subscriptionCustomer->getId(),
                'salesChannel' => $salesChannel->getId()
            ]);
        $this->assertNotEmpty($subscription);

        $item = $subscription->getItem();
        static::assertSame($product->getSku(), $item->getSku());

        return $subscription->getId();
    }

    /**
     * @depends testCreateAction
     * @param int $id
     * @return int
     */
    public function testUpdateAction($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_subscription_update', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        /** @var Customer $subscriptionCustomer */
        $subscriptionCustomer = $this->getReference('marello-customer-2');
        $submittedData = $this->getUpdateFormData($form, $subscriptionCustomer);

        $this->client->followRedirects(true);

        $this->client->request($form->getMethod(), $form->getUri(), $submittedData);
        $result  = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        return $id;
    }

    /**
     * @depends testUpdateAction
     * @param int $id
     */
    public function testViewAction($id)
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_subscription_view', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    /**
     * @depends testUpdateAction
     * @param int $id
     */
    public function testAddressAction($id)
    {
        /** @var Subscription $subscription */
        $subscription = $this->getContainer()->get('doctrine')
            ->getManagerForClass(Subscription::class)
            ->getRepository(Subscription::class)
            ->find($id);
        $this->client->request(
            'GET',
            $this->getUrl('marello_subscription_address', [
                'id'               => $subscription->getBillingAddress()->getId(),
                'typeId'           => 1,
                '_widgetContainer' => 'block',
            ])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    /**
     * @depends testUpdateAction
     * @param int $id
     */
    public function testUpdateAddressAction($id)
    {
        /** @var Subscription $subscription */
        $subscription = $this->getContainer()->get('doctrine')
            ->getManagerForClass(Subscription::class)
            ->getRepository(Subscription::class)
            ->find($id);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_subscription_updateaddress', [
                'id'               => $subscription->getBillingAddress()->getId(),
                '_widgetContainer' => 'dialog',
            ])
        );

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save')->form();
        $name = 'Han Solo';
        $lastName = 'Solo';

        $form['marello_address[firstName]'] = $name;
        $form['marello_address[lastName]'] = $lastName;

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    /**
     * @param Form $form
     * @param Customer $customer
     * @param SalesChannel $salesChannel
     * @param Product $product
     * @return array
     */
    private function getCreateFormData($form, $customer, $salesChannel, $product)
    {
        /** @var ShippingMethodProviderInterface $shippingMethodsProvider */
        $shippingMethodsProvider = $this->getContainer()->get('marello_shipping.shipping_method_provider');
        $shippingMethods = $shippingMethodsProvider->getShippingMethods();
        /** @var ShippingMethodInterface $shippingMethod */
        $shippingMethod = reset($shippingMethods);
        $shippingMethodTypes = $shippingMethod->getTypes();
        /** @var ShippingMethodTypeInterface $shippingMethodType */
        $shippingMethodType = reset($shippingMethodTypes);
        $submittedData = [
            'input_action' => 'save_and_stay',
            'marello_subscription' => [
                '_token' => $form['marello_subscription[_token]']->getValue(),
                'customer' => $customer->getId(),
                'salesChannel' => $salesChannel->getId(),
                'item' => $product->getId(),
                'billingAddress' => $this->getAddressFormData($customer->getPrimaryAddress()),
                'shippingAddress' => $this->getAddressFormData($customer->getPrimaryAddress()),
                'calculateShipping' => true,
                'shippingMethod' => $shippingMethod->getIdentifier(),
                'shippingMethodType' => $shippingMethodType->getIdentifier(),
                'cancelBeforeDuration' => 0
            ]
        ];

        return $submittedData;
    }
    /**
     * @param Form $form
     * @param Customer $customer
     * @return array
     */
    private function getUpdateFormData($form, $customer)
    {
        /** @var ShippingMethodProviderInterface $shippingMethodsProvider */
        $shippingMethodsProvider = $this->getContainer()->get('marello_shipping.shipping_method_provider');
        $shippingMethods = $shippingMethodsProvider->getShippingMethods();
        /** @var ShippingMethodInterface $shippingMethod */
        $shippingMethod = reset($shippingMethods);
        $shippingMethodTypes = $shippingMethod->getTypes();
        /** @var ShippingMethodTypeInterface $shippingMethodType */
        $shippingMethodType = reset($shippingMethodTypes);
        $submittedData = [
            'input_action' => 'save_and_stay',
            'marello_subscription_update' => [
                '_token' => $form['marello_subscription_update[_token]']->getValue(),
                'billingAddress' => $this->getAddressFormData($customer->getPrimaryAddress()),
                'shippingAddress' => $this->getAddressFormData($customer->getPrimaryAddress()),
                'calculateShipping' => true,
                'shippingMethod' => $shippingMethod->getIdentifier(),
                'shippingMethodType' => $shippingMethodType->getIdentifier()
            ]
        ];

        return $submittedData;
    }

    /**
     * @param MarelloAddress $address
     * @return array
     */
    private function getAddressFormData(MarelloAddress $address)
    {
        return [
            'namePrefix' => $address->getNamePrefix(),
            'firstName' => $address->getFirstName(),
            'middleName' => $address->getMiddleName(),
            'lastName' => $address->getLastName(),
            'nameSuffix' => $address->getNameSuffix(),
            'country' => $address->getCountryIso2(),
            'street' => $address->getStreet(),
            'street2' => $address->getStreet2(),
            'city' => $address->getCity(),
            'region' => $address->getRegionCode(),
            'region_text' => $address->getRegionText(),
            'postalCode' => $address->getPostalCode(),
            'phone' =>$address->getPhone(),
            'company' => $address->getCompany()
        ];
    }
}
