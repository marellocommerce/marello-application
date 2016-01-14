<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolation
 */
class InventoryControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductPricingData',
        ]);
    }

    public function testViewAction()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_inventory_inventory_view', ['id' => $this->getReference('marello-product-0')])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testUpdateActionAvailable()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_inventory_inventory_update', ['id' => $this->getReference('marello-product-0')])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }
}
