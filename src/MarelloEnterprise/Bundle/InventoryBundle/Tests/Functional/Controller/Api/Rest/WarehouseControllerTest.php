<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\Controller\Api\Rest;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadWarehouseData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WarehouseControllerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures([
            LoadWarehouseData::class
        ]);
    }

    /**
     * Tests deleting a additional warehouse
     */
    public function testDeleteExistingWarehouse()
    {
        $warehouseId = $this->getReference(LoadWarehouseData::WAREHOUSE_1_REF)->getId();

        $this->client->request(
            'DELETE',
            $this->getUrl('marelloenterprise_inventory_api_delete_warehouse', ['id' => $warehouseId])
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehouse_view', array('id' => $warehouseId))
        );

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, 404);
    }

    /**
     * Tests deleting a additional warehouse
     */
    public function testCannotDeleteDefaultWarehouse()
    {
        $warehouse = $this->getContainer()
            ->get('doctrine')
            ->getRepository('MarelloInventoryBundle:Warehouse')
            ->getDefault();

        $this->client->request(
            'DELETE',
            $this->getUrl('marelloenterprise_inventory_api_delete_warehouse', ['id' => $warehouse->getId()])
        );

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_FORBIDDEN);
        $this->assertEquals(
            '{"reason":"It is forbidden to delete default Warehouse"}',
            $this->client->getResponse()->getContent()
        );
    }
}
