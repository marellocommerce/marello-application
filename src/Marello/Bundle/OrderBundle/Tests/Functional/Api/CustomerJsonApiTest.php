<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadCustomerData;

class CustomerJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marellocustomers';

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadCustomerData::class
        ]);
    }

    /**
     * Test cget (getting a list of customers) of Customer entity
     */
    public function testGetListOfCustomers()
    {
        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertResponseCount(10, $response);
        $this->assertResponseContains('cget_customer_list.yml', $response);
    }

    /**
     * Test get customer by id
     */
    public function testGetCustomerById()
    {
        $customer = $this->getReference('marello-customer-1');
        $response = $this->get(
            ['entity' => self::TESTING_ENTITY, 'id' => $customer->getId()],
            []
        );

        $this->assertJsonResponse($response);
        $this->assertResponseContains('get_customer_by_id.yml', $response);
    }

    /**
     * Get a single customer by email
     */
    public function testGetCustomerFilteredByEmail()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('marello-customer-1');
        $response = $this->cget(
            ['entity' => self::TESTING_ENTITY],
            [
                'filter' => ['email' =>  $customer->getEmail() ]
            ]
        );

        $this->assertJsonResponse($response);
        $this->assertResponseCount(1, $response);
        $this->assertResponseContains('get_customer_by_email.yml', $response);
    }

    /**
     * Create a new customer
     */
    public function testCreateNewCustomer()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'customer_create_with_address.yml'
        );

        $this->assertJsonResponse($response);

        $responseContent = json_decode($response->getContent());

        /** @var Customer $customer */
        $customer = $this->getEntityManager()->find(Customer::class, $responseContent->data->id);
        $this->assertEquals($customer->getEmail(), $responseContent->data->attributes->email);
    }

    /**
     * Create a new customer
     */
    public function testCreateNewCustomerWithoutAddress()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'customer_create_without_address.yml'
        );

        $this->assertJsonResponse($response);

        $responseContent = json_decode($response->getContent());

        /** @var Customer $customer */
        $customer = $this->getEntityManager()->find(Customer::class, $responseContent->data->id);
        $this->assertEquals($customer->getEmail(), $responseContent->data->attributes->email);
    }

    /**
     * Update existing customer email address
     */
    public function testUpdateEmailExistingCustomer()
    {
        $existingCustomer = $this->getReference('marello-customer-1');
        $response = $this->patch(
            [
                'entity' => self::TESTING_ENTITY,
                'id' => $existingCustomer->getId()
            ],
            'customer_email_update.yml'
        );

        $this->assertJsonResponse($response);
        $responseContent = json_decode($response->getContent());

        /** @var Customer $customer */
        $customer = $this->getEntityManager()->find(Customer::class, $responseContent->data->id);
        $this->assertEquals($customer->getEmail(), 'mynewemailaddres@example.com');
    }

    /**
     * Update existing customer email address
     */
    public function testUpdateAddressExistingCustomer()
    {
        /** @var Customer $existingCustomer */
        $existingCustomer = $this->getReference('marello-customer-1');
        $response = $this->patch(
            [
                'entity' => self::TESTING_ENTITY,
                'id' => $existingCustomer->getId(),
                'association' => 'marelloaddresses',
            ],
            'customer_address_update.yml'
        );

        $this->assertJsonResponse($response);
        $responseContent = json_decode($response->getContent());
        /** @var Customer $customer */
        $customer = $this->getEntityManager()->find(Customer::class, $responseContent->data->id);
        $primaryAddress = $customer->getPrimaryAddress();
        self::assertSame('1215 Caldwell Road', $primaryAddress->getStreet());
        self::assertSame('Rochester', $primaryAddress->getCity());
        self::assertSame('777-777-777', $primaryAddress->getPhone());
        self::assertSame('14608', $primaryAddress->getPostalCode());
        self::assertSame('US', $primaryAddress->getCountryIso2());
        self::assertSame('US-NY', $primaryAddress->getRegion()->getCombinedCode());
    }
}
