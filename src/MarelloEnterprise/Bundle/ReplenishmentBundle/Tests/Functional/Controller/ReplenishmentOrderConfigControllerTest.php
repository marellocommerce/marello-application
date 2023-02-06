<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Tests\Functional\Controller;

use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadWarehouseData;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy\EqualDivision\EqualDivisionReplenishmentStrategy;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @outputBuffering enabled
 */
class ReplenishmentOrderConfigControllerTest extends WebTestCase
{
    const GRID_NAME = 'marello-products-grid';

    public function setUp(): void
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );

        $this->loadFixtures([
            LoadInventoryData::class,
            LoadWarehouseData::class,
        ]);
    }

    public function testCreateProductAutomatedEmpty()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_replenishment_order_config_create_step_one'));

        $form = $crawler->selectButton('Continue')->form();
        $formValues = $form->getPhpValues();
        $formValues['input_action'] = 'save_and_close';
        $formValues['marello_replenishment_order_step_one']['type'] = 'automated';
        $this->client->followRedirects(true);
        $crawler = $this->client->request(
            'POST',
            $form->getUri(),
            $formValues
        );

        $form = $crawler->selectButton('Save and Close')->form();
        $formValues = $form->getPhpValues();
        $formValues['marello_replenishment_order_config']['origins'] = (string) $this->getReference(LoadWarehouseData::WAREHOUSE_1_REF)->getId();
        $formValues['marello_replenishment_order_config']['destinations'] = (string) $this->getReference(LoadWarehouseData::WAREHOUSE_2_REF)->getId();
        $formValues['marello_replenishment_order_config']['strategy'] = EqualDivisionReplenishmentStrategy::IDENTIFIER;
        $formValues['marello_replenishment_order_config']['percentage'] = '10';
        $formValues['marello_replenishment_order_config']['products']['added'] = (string) $this->getReference(LoadProductData::PRODUCT_1_REF)->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertStringContainsString('No one Replenishment Order was created, because there are no selected products in selected origins', $crawler->html());
    }

    public function testCreateProductAutomated()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_replenishment_order_config_create_step_one'));

        $form = $crawler->selectButton('Continue')->form();
        $formValues = $form->getPhpValues();
        $formValues['input_action'] = 'save_and_close';
        $formValues['marello_replenishment_order_step_one']['type'] = 'automated';
        $this->client->followRedirects(true);
        $crawler = $this->client->request(
            'POST',
            $form->getUri(),
            $formValues
        );

        $form = $crawler->selectButton('Save and Close')->form();
        $formValues = $form->getPhpValues();
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $formValues['marello_replenishment_order_config']['origins'] = (string) $this->getContainer()->get(WarehouseRepository::class)->getDefault($aclHelper)->getId();
        $formValues['marello_replenishment_order_config']['destinations'] = (string) $this->getReference(LoadWarehouseData::WAREHOUSE_1_REF)->getId();
        $formValues['marello_replenishment_order_config']['strategy'] = EqualDivisionReplenishmentStrategy::IDENTIFIER;
        $formValues['marello_replenishment_order_config']['percentage'] = '10';
        $formValues['marello_replenishment_order_config']['products']['added'] = (string) $this->getReference(LoadProductData::PRODUCT_2_REF)->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertStringContainsString('Replenishment Order Config has been saved successfully', $crawler->html());
    }

    public function testCreateProductManual()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_replenishment_order_config_create_step_one'));

        $form = $crawler->selectButton('Continue')->form();
        $formValues = $form->getPhpValues();
        $formValues['input_action'] = 'save_and_close';
        $formValues['marello_replenishment_order_step_one']['type'] = 'manual';
        $this->client->followRedirects(true);
        $crawler = $this->client->request(
            'POST',
            $form->getUri(),
            $formValues
        );

        $form = $crawler->selectButton('Save and Close')->form();
        $formValues = $form->getPhpValues();

        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $formValues['marello_replenishment_order_config_manual']['manualItems'][0]['product'] = (string) $this->getReference(LoadProductData::PRODUCT_1_REF)->getId();
        $formValues['marello_replenishment_order_config_manual']['manualItems'][0]['origin'] = (string) $this->getContainer()->get(WarehouseRepository::class)->getDefault($aclHelper)->getId();
        $formValues['marello_replenishment_order_config_manual']['manualItems'][0]['destination'] = (string) $this->getReference(LoadWarehouseData::WAREHOUSE_1_REF)->getId();
        $formValues['marello_replenishment_order_config_manual']['manualItems'][0]['quantity'] = '1';
        $formValues['marello_replenishment_order_config_manual']['manualItems'][1]['product'] = (string) $this->getReference(LoadProductData::PRODUCT_2_REF)->getId();
        $formValues['marello_replenishment_order_config_manual']['manualItems'][1]['origin'] = (string) $this->getContainer()->get(WarehouseRepository::class)->getDefault($aclHelper)->getId();
        $formValues['marello_replenishment_order_config_manual']['manualItems'][1]['destination'] = (string) $this->getReference(LoadWarehouseData::WAREHOUSE_2_REF)->getId();
        $formValues['marello_replenishment_order_config_manual']['manualItems'][1]['quantity'] = '';
        $formValues['marello_replenishment_order_config_manual']['manualItems'][1]['allQuantity'] = '1';

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertStringContainsString('Replenishment Order Config has been saved successfully', $crawler->html());
    }
}
