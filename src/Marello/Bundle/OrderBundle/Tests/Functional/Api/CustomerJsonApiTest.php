<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Api;

use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;

use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadCustomerData;

class CustomerJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'customers';

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
//        $requestData = [
//            'data' => [
//                'type' => self::TESTING_ENTITY,
//                'attributes' => [
//                    'firstName' => 'John',
//                    'lastName'  => 'Doe',
//                    'email'     => 'new_customer@example.com',
//                ],
//                'relationships' => [
//                    'primaryAddress' => [
//                        'data' => ['type' => 'marelloaddresses', 'id'  => '8da4d8e7-6b25-4c5c-8075-b510f7bbb84f']
//                    ]
//                ]
//            ],
//            'included' => [
//                [
//                    'type' => 'marelloaddresses',
//                    'id'   => '8da4d8e7-6b25-4c5c-8075-b510f7bbb84f',
//                    'attributes' =>  [
//                        'firstName'  => 'John',
//                        'lastName'   => 'Doe',
//                        'street'     => 'Torenallee 20',
//                        'city'       => 'Eindhoven',
//                        'postalCode' => '5617 BC',
//                        'company'    => 'Madia Inc'
//                    ],
//                    'relationships' => [
//                        'country' => [
//                            'data' => ['type' => 'countries', 'id' => 'US']
//                        ],
//                        'region' => [
//                            'data' => ['type' => 'regions', 'id' => 'US-NY']
//                        ]
//                    ]
//                ]
//            ]
//        ];
//        $requestData = [
//            'data' => [
//                'type' => 'marelloaddresses',
//                'attributes' =>  [
//                    'firstName'  => 'John',
//                    'lastName'   => 'Doe',
//                    'street'     => 'Torenallee 20',
//                    'city'       => 'Eindhoven',
//                    'postalCode' => '5617 BC'
//                ],
//                'relationships' => [
//                    'country' => [
//                        'data' => ['type' => Country::class, 'id' => 'US']
//                    ],
//                    'region' => [
//                            'data' => ['type' => Region::class, 'id' => 'US-NY']
//                    ]
//                ]
//            ]
//        ];
        $response = $this->post(
            ['entity' => 'marelloaddresses'],
            'address_create.yml'
        );

//        $response = $this->post(
//            ['entity' => 'marelloaddresses'],
//            $requestData
//        );

        $this->assertJsonResponse($response);

        $responseContent = json_decode($response->getContent());

        /** @var Customer $customer */
        $customer = $this->getEntityManager()->find(Customer::class, $responseContent->data->id);
        $this->assertEquals($customer->getEmail(), $responseContent->data->attributes->email);

        $address = $customer->getPrimaryAddress();
        var_dump($address->getId());
        var_dump($address->getCity());
    }
}
