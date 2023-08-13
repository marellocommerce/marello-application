<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Controller;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Tests\Functional\DataFixtures\LoadPurchaseOrderData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ExpectedInventoryItemControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadPurchaseOrderData::class,
            LoadInventoryData::class,
        ]);
    }

    public function testViewAction()
    {
        /** @var PurchaseOrder $po */
        $po = $this->getContainer()->get('doctrine')->getRepository(InventoryItem::class)->findOneBy([]);
        $this->client->request(
            'GET',
            $this->getUrl('marello_inventory_expected_inventory_item_view', ['id' => $po->getId()])
        );

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }
}
