<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PurchaseOrderControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient($this->generateBasicAuthHeader());
    }

    /** @test */
    public function testIndexAction()
    {
        $this->client->request('GET', $this->getUrl('marello_purchaseorder_purchaseorder_index'));

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testViewAction()
    {
        
    }
}
