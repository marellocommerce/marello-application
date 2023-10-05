<?php

namespace Marello\Bundle\CustomerBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerData;

class CustomerJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marellocustomers';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadOrganization::class,
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
     * Email has become the id for the Customer Entity
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
        $this->assertNotNull($customer->getPrimaryAddress(), 'Customer with Address');
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
        $this->assertNull($customer->getPrimaryAddress(), 'Customer without Address');
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

    /**
     * Try creating customer with already existing email address
     */
    public function testCreateDuplicateCustomer()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'customer_create_with_address.yml',
            [],
            false
        );

        $this->assertJsonResponse($response);

        $entityType = self::extractEntityType(['entity' => self::TESTING_ENTITY]);
        self::assertApiResponseStatusCodeEquals(
            $response,
            Response::HTTP_BAD_REQUEST,
            $entityType,
            'post'
        );
        self::assertResponseContentTypeEquals($response, $this->getResponseContentType());
    }

    /**
     * @param array $parameters
     * @return string
     */
    private static function extractEntityType(array $parameters): string
    {
        if (empty($parameters['entity'])) {
            return 'unknown';
        }

        return $parameters['entity'];
    }
}
