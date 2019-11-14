<?php

namespace Marello\Bundle\SubscriptionBundle\Tests\Functional\Controller;

use Marello\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerData;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\SubscriptionBundle\Form\Type\SubscriptionType;
use Marello\Bundle\SubscriptionBundle\Tests\Functional\DataFixtures\LoadSubscriptionProductData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionAjaxControllerTest extends WebTestCase
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

    public function testFormChangesAction()
    {
        $this->client->request(
            'POST',
            $this->getUrl('marello_subscription_form_changes'),
            [
                SubscriptionType::BLOCK_PREFIX => [
                    'customer' => $this->getReference('marello-customer-1')->getId(),
                    'salesChannel' => $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId(),
                    'item' => $this->getReference(LoadSubscriptionProductData::SUBSCRIPTION_PRODUCT_1_REF)->getId()
                ]
            ]
        );

        $response = $this->client->getResponse();
        $this->assertInstanceOf(Response::class, $response);

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertArrayHasKey('billingAddress', $result);
        $this->assertArrayHasKey('shippingAddress', $result);
        $this->assertArrayHasKey('possibleShippingMethods', $result);
    }
}
