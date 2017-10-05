<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\Controller\Api\Rest;

use MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadWarehouseGroupData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WarehouseGroupControllerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures([
            LoadWarehouseGroupData::class
        ]);
    }

    public function testDeleteExistingWarehouseGroup()
    {
        $id = $this->getReference(LoadWarehouseGroupData::ADDITIONAL_WAREHOUSE_GROUP)->getId();

        $this->client->request(
            'DELETE',
            $this->getUrl('marelloenterprise_inventory_api_delete_warehousegroup', ['id' => $id])
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehousegroup_view', array('id' => $id))
        );

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, 404);
    }

    public function testCannotDeleteSystemWarehouseGroup()
    {
        $warehouseGroup = $this->getContainer()
            ->get('doctrine')
            ->getRepository('MarelloInventoryBundle:WarehouseGroup')
            ->findSystemWarehouseGroup();

        $this->client->request(
            'DELETE',
            $this->getUrl('marelloenterprise_inventory_api_delete_warehousegroup', ['id' => $warehouseGroup->getId()])
        );

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_FORBIDDEN);
        $this->assertEquals(
            '{"reason":"It is forbidden to delete system Warehouse Group"}',
            $this->client->getResponse()->getContent()
        );
    }
}
