<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadWarehouseData;

/**
 * @dbIsolation
 */
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
        $warehouseId = $this->getReference(LoadWarehouseData::ADDITIONAL_WAREHOUSE)->getId();

        $this->client->request(
            'DELETE',
            $this->getUrl('marelloenterprise_inventory_api_delete_warehouse', ['id' => $warehouseId])
        );

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
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

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals(
            '{"code":500,"message":"Cannot delete default warehouse."}',
            $this->client->getResponse()->getContent()
        );
    }
}
