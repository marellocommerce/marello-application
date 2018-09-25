<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\Controller;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Form\Type\WarehouseType;
use MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadWarehouseData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class WarehouseControllerTest extends WebTestCase
{
    const LABEL = 'Warehouse';
    const CODE = 'warehouse';
    const COUNTRY = 'NL';
    const STREET = 'some street';
    const STREET2 = 'some street2';
    const CITY = 'some city';
    const POSTAL_CODE = '2222';
    const PHONE = '11111';

    const UPDATED_LABEL = 'Updated Warehouse';
    const UPDATED_CODE = 'updated_warehouse';
    const UPDATED_COUNTRY = 'UA';
    const UPDATED_STREET = 'updated some street';
    const UPDATED_STREET2 = 'updated some street2';
    const UPDATED_CITY = 'updated some city';
    const UPDATED_POSTAL_CODE = '1212';
    const UPDATED_PHONE = '33333';

    const SAVE_MESSAGE = 'Warehouse has been saved successfully';

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
            LoadWarehouseData::class,
        ]);
    }

    public function testIndex()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehouse_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertContains('marelloenterprise-inventory-warehouses-grid', $crawler->html());
        $this->assertNotEmpty('Create Warehouse', $crawler->html());
    }

    /**
     * @return int
     */
    public function testCreate()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehouse_create')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertWarehouseSave(
            $crawler,
            self::LABEL,
            self::CODE,
            self::COUNTRY,
            self::STREET,
            self::STREET2,
            self::CITY,
            self::POSTAL_CODE,
            self::PHONE
        );

        /** @var Warehouse $warehouse */
        $warehouse = $this->getContainer()->get('doctrine')
            ->getManagerForClass('MarelloInventoryBundle:Warehouse')
            ->getRepository('MarelloInventoryBundle:Warehouse')
            ->findOneBy(['code' => self::CODE]);
        $this->assertNotEmpty($warehouse);

        return $warehouse->getId();
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
            $this->getUrl('marelloenterprise_inventory_warehouse_update', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertWarehouseSave(
            $crawler,
            self::UPDATED_LABEL,
            self::UPDATED_CODE,
            self::UPDATED_COUNTRY,
            self::UPDATED_STREET,
            self::UPDATED_STREET2,
            self::UPDATED_CITY,
            self::UPDATED_POSTAL_CODE,
            self::UPDATED_PHONE
        );

        /** @var Warehouse $warehouse */
        $warehouse = $this->getContainer()->get('doctrine')
            ->getManagerForClass('MarelloInventoryBundle:Warehouse')
            ->getRepository('MarelloInventoryBundle:Warehouse')
            ->findOneBy(['code' => self::UPDATED_CODE]);
        $this->assertNotEmpty($warehouse);

        return $warehouse->getId();
    }

    /**
     * @depends testUpdate
     * @param int $id
     */
    public function testView($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehouse_view', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertViewPage(
            $crawler->html(),
            self::LABEL,
            self::UPDATED_CODE,
            self::UPDATED_STREET,
            self::UPDATED_STREET2,
            self::UPDATED_CITY,
            self::UPDATED_POSTAL_CODE,
            self::UPDATED_PHONE
        );
    }

    /**
     * @param Crawler $crawler
     * @param string $label
     * @param string $code
     * @param string $country
     * @param string $street
     * @param bool $street2
     * @param bool $city
     * @param int $postalCode
     * @param int $phone
     */
    protected function assertWarehouseSave(
        Crawler $crawler,
        $label,
        $code,
        $country,
        $street,
        $street2,
        $city,
        $postalCode,
        $phone
    ) {
        $token = $this->getContainer()->get('security.csrf.token_manager')
            ->getToken(WarehouseType::NAME)->getValue();

        $formData = [
            'input_action' => '{"route":"marelloenterprise_inventory_warehouse_view","params":{"id":"$id"}}',
            WarehouseType::NAME => [
                'label' => $label,
                'code' => $code,
                'address' => [
                    'country' => $country,
                    'street' => $street,
                    'street2' => $street2,
                    'city' => $city,
                    'postalCode' => $postalCode,
                    'phone' => $phone,
                ],
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
        $this->assertViewPage($html, $label, $code, $street, $street2, $city, $postalCode, $phone);
    }
    
    /**
     * @param string $html
     * @param string $label
     * @param string $code
     * @param string $street
     * @param bool $street2
     * @param bool $city
     * @param int $postalCode
     * @param int $phone
     */
    protected function assertViewPage(
        $html,
        $label,
        $code,
        $street,
        $street2,
        $city,
        $postalCode,
        $phone
    ) {
        $this->assertContains($label, $html);
        $this->assertContains($code, $html);
        $this->assertContains($street, $html);
        $this->assertContains($street2, $html);
        $this->assertContains($city, $html);
        $this->assertContains($postalCode, $html);
        $this->assertContains($phone, $html);
    }
}
