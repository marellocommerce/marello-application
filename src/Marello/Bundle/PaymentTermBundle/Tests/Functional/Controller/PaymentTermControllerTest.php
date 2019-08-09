<?php

namespace Marello\Bundle\PaymenttermBundle\Tests\Functional\Controller;

use Marello\Bundle\PaymentTermBundle\Tests\Functional\DataFixtures\LoadPaymentTermsData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PaymentTermControllerTest extends WebTestCase
{
    const NAME = 'name';
    const DESCRIPTION = 'description';

    const UPDATED_NAME = 'updatedName';
    const UPDATED_DESCRIPTION = '';

    const SAVE_MESSAGE = 'Sales Channel Group has been saved successfully';

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadPaymentTermsData::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testIndex()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_paymentterm_paymentterm_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertContains('marello-paymentterm-grid', $crawler->html());
    }
}
