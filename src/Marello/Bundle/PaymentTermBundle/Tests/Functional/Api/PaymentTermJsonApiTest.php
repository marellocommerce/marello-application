<?php

namespace Marello\Bundle\PaymentTermBundle\Tests\Functional\Api;

use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Marello\Bundle\PaymentTermBundle\Tests\Functional\DataFixtures\LoadPaymentTermsData;
use Symfony\Component\HttpFoundation\Response;

class PaymentTermJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marellopaymentterms';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadPaymentTermsData::class
        ]);
    }

    /**
     * Test cget (getting a list of payment terms) of PaymentTerm entity
     */
    public function testGetListOfPaymentTerms()
    {
        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);


        $this->assertResponseCount(4, $response);
        $this->assertResponseContains('cget_payment_terms_list.yml', $response);
    }

    /**
     * Test get company by id
     */
    public function testGetPaymentTermById()
    {
        /** @var PaymentTerm $paymentTerm */
        $paymentTerm = $this->getReference(LoadPaymentTermsData::PAYMENT_TERM_1_REF);
        $response = $this->get(
            ['entity' => self::TESTING_ENTITY, 'id' => $paymentTerm->getId()],
            []
        );

        $this->assertJsonResponse($response);
        $this->assertResponseContains('get_payment_term_by_id.yml', $response);
    }

    /**
     * Test Create new PaymentTerm
     */
    public function testCreateNewPaymentTerm()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'payment_term_create.yml'
        );

        $this->assertJsonResponse($response);
        $responseContent = json_decode($response->getContent());

        /** @var PaymentTerm $paymentTerm */
        $company = $this->getEntityManager()->find(PaymentTerm::class, $responseContent->data->id);
        $this->assertEquals($company->getCode(), $responseContent->data->attributes->code);
    }

    /**
     * Test update PaymentTerm
     */
    public function testUpdatePaymentTerm()
    {
        /** @var PaymentTerm $existingPaymentTerm */
        $existingPaymentTerm = $this->getReference(LoadPaymentTermsData::PAYMENT_TERM_1_REF);
        $existingPaymentTermTerm = $existingPaymentTerm->getTerm();
        $response = $this->patch(
            [
                'entity' => self::TESTING_ENTITY,
                'id' => $existingPaymentTerm->getId()
            ],
            'payment_term_update.yml'
        );

        $this->assertJsonResponse($response);
        $responseContent = json_decode($response->getContent());

        /** @var PaymentTerm $paymentTerm */
        $paymentTerm = $this->getEntityManager()->find(PaymentTerm::class, $responseContent->data->id);
        $this->assertEquals($paymentTerm->getTerm(), $responseContent->data->attributes->term);
        $this->assertNotEquals($paymentTerm->getTerm(), $existingPaymentTermTerm);
    }
}
