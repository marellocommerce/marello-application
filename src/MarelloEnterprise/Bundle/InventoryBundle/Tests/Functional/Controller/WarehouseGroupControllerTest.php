<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\Controller;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGroupType;
use MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadWarehouseData;
use MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadWarehouseGroupData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class WarehouseGroupControllerTest extends WebTestCase
{
    const NAME = 'name';
    const DESCRIPTION = 'description';

    const UPDATED_NAME = 'updatedName';
    const UPDATED_DESCRIPTION = '';

    const SAVE_MESSAGE = 'Warehouse Group has been saved successfully';

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
            LoadWarehouseGroupData::class,
        ]);
    }

    public function testIndex()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehousegroup_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertContains('marelloenterprise-inventory-warehousegroups-grid', $crawler->html());
    }

    /**
     * @return int
     */
    public function testCreate()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehousegroup_create')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertWarehouseGroupSave(
            $crawler,
            self::NAME,
            self::DESCRIPTION,
            [
                $this->getReference(LoadWarehouseData::WAREHOUSE_1_REF),
                $this->getReference(LoadWarehouseData::WAREHOUSE_2_REF)
            ]
        );

        /** @var WarehouseGroup $warehouseGroup */
        $warehouseGroup = $this->getContainer()->get('doctrine')
            ->getManagerForClass('MarelloInventoryBundle:WarehouseGroup')
            ->getRepository('MarelloInventoryBundle:WarehouseGroup')
            ->findOneBy(['name' => self::NAME]);
        $this->assertNotEmpty($warehouseGroup);

        return $warehouseGroup->getId();
    }

    /**
     * @param int $id
     * @return int
     * @depends testCreate
     */
    public function testUpdate($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehousegroup_update', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertWarehouseGroupSave(
            $crawler,
            self::UPDATED_NAME,
            self::UPDATED_DESCRIPTION,
            [
                $this->getReference(LoadWarehouseData::WAREHOUSE_2_REF),
                $this->getReference(LoadWarehouseData::WAREHOUSE_3_REF)
            ]
        );

        /** @var Warehouse $warehouse */
        $warehouse = $this->getContainer()->get('doctrine')
            ->getManagerForClass('MarelloInventoryBundle:Warehouse')
            ->getRepository('MarelloInventoryBundle:Warehouse')
            ->findOneBy(['code' => $this->getReference(LoadWarehouseData::WAREHOUSE_1_REF)->getCode()]);
        $this->assertTrue($warehouse->getGroup()->isSystem());

        return $id;
    }

    /**
     * @depends testUpdate
     * @param int $id
     */
    public function testView($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehousegroup_view', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $warehouseLabels = array_map(
            function (Warehouse $warehouse) {
                return $warehouse->getLabel();
            },
            [
                $this->getReference(LoadWarehouseData::WAREHOUSE_2_REF),
                $this->getReference(LoadWarehouseData::WAREHOUSE_3_REF)
            ]
        );

        $this->assertViewPage($crawler->html(), self::UPDATED_NAME, self::UPDATED_DESCRIPTION, $warehouseLabels);
    }

    /**
     * @param Crawler $crawler
     * @param string $name
     * @param string $description
     * @param Warehouse[] $warehouses
     */
    protected function assertWarehouseGroupSave(Crawler $crawler, $name, $description, array $warehouses)
    {
        $token = $this->getContainer()->get('security.csrf.token_manager')
            ->getToken(WarehouseGroupType::NAME)->getValue();

        $warehouseIds = array_map(
            function (Warehouse $warehouse) {
                return $warehouse->getId();
            },
            $warehouses
        );

        $warehouseLabels = array_map(
            function (Warehouse $warehouse) {
                return $warehouse->getLabel();
            },
            $warehouses
        );

        $formData = [
            'input_action' => '{"route":"marelloenterprise_inventory_warehousegroup_view","params":{"id":"$id"}}',
            WarehouseGroupType::NAME => [
                'name' => $name,
                'description' => $description,
                'warehouses' => implode(',', $warehouseIds),
                '_token' => $token,
            ],
        ];

        $form = $crawler->selectButton('Save and Close')->form();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formData);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        $this->assertContains(self::SAVE_MESSAGE, $html);
        $this->assertViewPage($html, $name, $description, $warehouseLabels);
    }
    
    /**
     * @param string $html
     * @param string $name
     * @param string $description
     * @param array $warehouseLabels
     */
    protected function assertViewPage($html, $name, $description, array $warehouseLabels)
    {
        $this->assertContains($name, $html);
        $this->assertContains($description ? : 'N/A', $html);
        $this->assertContains('marelloenterprise-inventory-group-warehouses-grid', $html);
        foreach ($warehouseLabels as $label) {
            $this->assertContains($label, $html);
        }
    }
}
