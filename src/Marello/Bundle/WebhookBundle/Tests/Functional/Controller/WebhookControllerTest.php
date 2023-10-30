<?php

namespace Marello\Bundle\WebhookBundle\Tests\Functional\Controller;

use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

class WebhookControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_webhook_index'));
        $result = $this->client->getResponse();
        $this->assertStringContainsString('marello-webhook-grid', $crawler->html());
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    public function testCreateNewWebhook()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_webhook_create'));
        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'Webhook 1';
        $form['input_action'] = '{"route":"marello_webhook_view","params":{"id":"$id"}}';
        $form['marello_webhook_webhook[name]'] = $name;
        $form['marello_webhook_webhook[callbackUrl]'] = 'http://example.com';
        $form['marello_webhook_webhook[secret]'] = 'some-secret-code';
        $form['marello_webhook_webhook[enabled]'] = '1';
        $form['marello_webhook_webhook[event]'] = 'marello_inventory.inventory.update';


        $this->client->followRedirects();
        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertStringContainsString('Webhook saved', $crawler->html());
        $this->assertStringContainsString($name, $crawler->html());
    }
}
