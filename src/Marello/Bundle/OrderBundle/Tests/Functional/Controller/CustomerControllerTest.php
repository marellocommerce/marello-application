<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Controller;

use Marello\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCompanyData;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadCustomerData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

class CustomerControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadCustomerData::class,
            LoadCompanyData::class
        ]);
    }

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_customer_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    /**
     * @return int
     */
//    public function testCreate()
//    {
//        $crawler = $this->client->request('GET', $this->getUrl('marello_order_customer_create'));
//        $result  = $this->client->getResponse();
//        $this->assertHtmlResponseStatusCodeEquals($result, 200);
//
//        /** @var Form $form */
//        $form = $crawler->selectButton('Save and Close')->form();
//
//        $name = 'Captain';
//        $email = 'captain@obvious.com';
//        $formValues = $form->getPhpValues();
//        var_dump($formValues);
//        $formValues['marello_customer']['firstName'] = $name;
//        $formValues['marello_customer']['middleName'] = 'von';
//        $formValues['marello_customer']['lastName'] = 'Obvious';
//        $formValues['marello_customer']['email'] = $email;
//        $formValues['marello_customer']['taxIdentificationNumber'] = '123456';
//        $formValues['marello_customer']['primaryAddress']['firstName'] = 'Captain';
//        $formValues['marello_customer']['primaryAddress']['middleName'] = 'von';
//        $formValues['marello_customer']['primaryAddress']['lastName'] = 'Obvious';
//        $formValues['marello_customer']['primaryAddress']['country'] = 'US';
//        $formValues['marello_customer']['primaryAddress']['street'] = '5th Avenue';
//        $formValues['marello_customer']['primaryAddress']['street2'] = 'enabled';
//        $formValues['marello_customer']['primaryAddress']['city'] = 'New York City';
//        $formValues['marello_customer']['primaryAddress']['region'] = 'US-NY';
//        $formValues['marello_customer']['primaryAddress']['postalCode'] = '44444';
//        $formValues['marello_customer']['primaryAddress']['phone'] = '123456789';
//        $formValues['marello_customer']['primaryAddress']['company'] = 'AcmeWorld';
//        $formValues['marello_customer']['shippingAddress']['firstName'] = 'Captain';
//        $formValues['marello_customer']['shippingAddress']['middleName'] = 'von';
//        $formValues['marello_customer']['shippingAddress']['lastName'] = 'Obvious';
//        $formValues['marello_customer']['shippingAddress']['country'] = 'US';
//        $formValues['marello_customer']['shippingAddress']['street'] = '5th Avenue';
//        $formValues['marello_customer']['shippingAddress']['city'] = 'New York City';
//        $formValues['marello_customer']['shippingAddress']['region'] = 'US-NY';
//        $formValues['marello_customer']['shippingAddress']['postalCode'] = '4444444';
//        $formValues['marello_customer']['shippingAddress']['phone'] = '123456789';
//        $formValues['marello_customer']['shippingAddress']['company'] = 'AcmeWorld';
//
//        $this->client->followRedirects(true);
//        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);
//        $result  = $this->client->getResponse();
//        var_dump($result->getContent());
//        self::assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
//        $this->assertContains('Customer saved', $crawler->html());
//        $this->assertContains($name, $crawler->html());
//        $this->assertContains($email, $crawler->html());
//
//
//        /** @var Customer $customer */
//        $customer = $this->getContainer()->get('doctrine')
//            ->getManagerForClass('MarelloOrderBundle:Customer')
//            ->getRepository('MarelloOrderBundle:Customer')
//            ->findOneBy(['email' => $email]);
//        $this->assertNotEmpty($customer);
//        static::assertSame($email, $customer->getEmail());
//
//        return $customer->getId();
//    }
//
//    /**
//     * @depends testCreate
//     * @param int $id
//     */
//    public function testView($id)
//    {
//        $crawler = $this->client->request(
//            'GET',
//            $this->getUrl('marello_order_customer_view', ['id' => $id])
//        );
//
//        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
//        $this->assertContains('captain@obvious.com', $crawler->html());
//    }
//
//    /**
//     * @param string $id
//     * @depends testCreate
//     */
//    public function testUpdateCustomer($id)
//    {
//        $crawler     = $this->client->request(
//            'GET',
//            $this->getUrl('marello_order_customer_update', ['id' => $id])
//        );
//        $result      = $this->client->getResponse();
//        self::assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
//
//        /** @var Form $form */
//        $form                                              = $crawler->selectButton('Save and Close')->form();
//        $name                                              = 'name' . self::generateRandomString();
//        $form['marello_customer[firstName]']                = $name;
//
//        $this->client->followRedirects(true);
//        $crawler = $this->client->submit($form);
//        $result = $this->client->getResponse();
//
//        self::assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
//        self::assertContains('Customer saved', $crawler->html());
//
//        /** @var Customer $customer */
//        $customer = $this->getContainer()->get('doctrine')
//            ->getManagerForClass('MarelloOrderBundle:Customer')
//            ->getRepository('MarelloOrderBundle:Customer')
//            ->find($id);
//
//        self::assertSame($name, $customer->getFirstName());
//    }
}
