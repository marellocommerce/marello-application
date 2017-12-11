<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Controller\Api\Rest;

use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadCustomerData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\User;

class CustomerControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateWsseAuthHeader()
        );

        $this->loadFixtures([
            LoadCustomerData::class
        ]);
    }


    /**
     * @test
     */
    public function testCreate()
    {

        $data = [
            'firstName' => 'John',
            'lastName'  => 'Doe',
            'email'     => 'new_customer@example.com',
            'primaryAddress'   => [
                'firstName'  => 'John',
                'lastName'   => 'Doe',
                'country'    => 'NL',
                'street'     => 'Torenallee 20',
                'city'       => 'Eindhoven',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
                'company'    => 'Madia Inc'
            ],
//            'organization' => $this->getUser()->getOrganization()->getId()
        ];

        $this->client->request(
            'POST',
            $this->getUrl('marello_customer_api_post_customer'),
            $data
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);
    }

    /**
     * @test
     */
    public function testCget()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_customer_api_get_customers')
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        $this->assertCount(10, json_decode($response->getContent(), true));
    }

    /**
     * @test
     *
     * @depends testCreate
     */
    public function getCustomerByEmailFromApi()
    {
        $email = 'new_customer@example.com';

        $this->client->request(
            'GET',
            $this->getUrl('marello_customer_api_get_customer_by_email', [
                'email' => $email
            ])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        $decodedResponse = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('customer', $decodedResponse);
        $this->assertEquals('new_customer@example.com', $decodedResponse['customer']['email']);
    }

    /**
     * @test
     */
    public function testGetNonExistingCustomerShouldReturnNotFound()
    {
        $email = 'notexisting@customer.com';

        $this->client->request(
            'GET',
            $this->getUrl('marello_customer_api_get_customer_by_email', [
                'email' => $email
            ])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);

        $decodedResponse = json_decode($response->getContent(), true);

        $this->assertArrayNotHasKey('customer', $decodedResponse);
        $this->assertArrayHasKey('message', $decodedResponse);
        $this->assertEquals('Customer with email notexisting@customer.com not found', $decodedResponse['message']);
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return $this->getEntityManager()->getRepository('OroUserBundle:User')->findOneByUsername(self::USER_NAME);
    }

    /**
     * @return ObjectManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
