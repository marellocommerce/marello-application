<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\Controller;

use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesChannelGroupData;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseChannelGroupLinkType;
use MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadWarehouseGroupData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class WarehouseChannelGroupLinkControllerTest extends WebTestCase
{
    const SAVE_MESSAGE = 'Warehouse Channel Group Link has been saved successfully';

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
            LoadSalesChannelGroupData::class,
            LoadWarehouseGroupData::class
        ]);
    }

    public function testIndex()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehousechannelgrouplink_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertContains('marelloenterprise-inventory-warehousechannelgrouplinks-grid', $crawler->html());
    }

    /**
     * @return int
     */
    public function testCreate()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehousechannelgrouplink_create')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertWarehouseChannelGroupLinkSave(
            $crawler,
            $this->getReference(LoadWarehouseGroupData::ADDITIONAL_WAREHOUSE_GROUP),
            [],
            [
                $this->getReference(LoadSalesChannelGroupData::CHANNELGROUP_2_REF),
                $this->getReference(LoadSalesChannelGroupData::CHANNELGROUP_3_REF)
            ]
        );

        /** @var WarehouseChannelGroupLink $link */
        $link = $this->getContainer()->get('doctrine')
            ->getManagerForClass('MarelloInventoryBundle:WarehouseChannelGroupLink')
            ->getRepository('MarelloInventoryBundle:WarehouseChannelGroupLink')
            ->findOneBy(['system' => false]);
        $this->assertNotEmpty($link);

        return $link->getId();
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
            $this->getUrl('marelloenterprise_inventory_warehousechannelgrouplink_update', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertWarehouseChannelGroupLinkSave(
            $crawler,
            $this->getReference(LoadWarehouseGroupData::ADDITIONAL_WAREHOUSE_GROUP),
            [
                $this->getReference(LoadSalesChannelGroupData::CHANNELGROUP_3_REF)
            ],
            [
                $this->getReference(LoadSalesChannelGroupData::CHANNELGROUP_2_REF),
                $this->getReference(LoadSalesChannelGroupData::CHANNELGROUP_4_REF)
            ]
        );
    }

    /**
     * @param Crawler $crawler
     * @param WarehouseGroup $warehouseGroup
     * @param SalesChannelGroup[] $removeChannelGroups
     * @param SalesChannelGroup[] $addChannelGroups
     */
    protected function assertWarehouseChannelGroupLinkSave(
        Crawler $crawler,
        WarehouseGroup $warehouseGroup,
        array $removeChannelGroups,
        array $addChannelGroups
    ) {
        $token = $this->getContainer()->get('security.csrf.token_manager')
            ->getToken(WarehouseChannelGroupLinkType::NAME)->getValue();

        $addChannelGroupIds = array_map(
            function (SalesChannelGroup $channelGroup) {
                return $channelGroup->getId();
            },
            $addChannelGroups
        );
        $removeChannelGroupIds = array_map(
            function (SalesChannelGroup $channelGroup) {
                return $channelGroup->getId();
            },
            $removeChannelGroups
        );

        $formData = [
            'input_action' =>
                '{"route":"marelloenterprise_inventory_warehousechannelgrouplink_index"}',
            WarehouseChannelGroupLinkType::NAME => [
                'warehouseGroup' => $warehouseGroup,
                'addSalesChannelGroups' => implode(',', $addChannelGroupIds),
                'removeSalesChannelGroups' => implode(',', $removeChannelGroupIds),
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
        $this->assertContains($warehouseGroup->getName(), $html);
        foreach ($addChannelGroups as $channelGroup) {
            $this->assertContains($channelGroup->getName(), $html);
        }
    }
}
