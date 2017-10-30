<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\Controller\Api\Rest;

use MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadWarehouseChannelGroupLinkData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WarehouseChannelGroupLinkControllerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures([
            LoadWarehouseChannelGroupLinkData::class
        ]);
    }

    public function testDeleteExistingWarehouseGroup()
    {
        $id = $this->getReference(LoadWarehouseChannelGroupLinkData::ADDITIONAL_WAREHOUSE_CHANNELGROUP_LINK)->getId();

        $this->client->request(
            'DELETE',
            $this->getUrl('marelloenterprise_inventory_api_delete_warehousechannelgrouplink', ['id' => $id])
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehousechannelgrouplink_update', array('id' => $id))
        );

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, 404);
    }
}
