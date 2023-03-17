<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Tests\Functional\Controller;

use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type\ReplenishmentOrderConfigManualType;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ReplenishmentOrderConfigAjaxControllerTest extends WebTestCase
{
    const ITEMS_FIELD = 'manualItems';
    const IDENTIFIER_PREFIX = 'item-id-';

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadInventoryData::class,
        ]);
    }

    public function testFormChangesAction()
    {
        $arrayKeys = ['unit', 'availableQuantity'];
        $productIds = [
            $this->getReference(LoadProductData::PRODUCT_1_REF)->getId(),
            $this->getReference(LoadProductData::PRODUCT_2_REF)->getId(),
        ];

        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $defaultWarehouseId = (string) $this->getContainer()->get(WarehouseRepository::class)->getDefault($aclHelper)->getId();
        $this->client->request(
            'POST',
            $this->getUrl('marello_replenishment_form_changes'),
            [
                ReplenishmentOrderConfigManualType::BLOCK_PREFIX => [
                    'manualItems' => [
                        ['product' => $productIds[0], 'origin' => $defaultWarehouseId],
                        ['product' => $productIds[1], 'origin' => $defaultWarehouseId],
                    ]
                ]
            ]
        );

        $response = $this->client->getResponse();
        $this->assertInstanceOf(Response::class, $response);

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertArrayHasKey(self::ITEMS_FIELD, $result);
        $this->assertCount(count($productIds), $result[self::ITEMS_FIELD]);
        foreach ($productIds as $key => $id) {
            $identifier = $this->getIdentifier($key);
            $this->assertArrayHasKey($identifier, $result[self::ITEMS_FIELD]);
            foreach ($arrayKeys as $arrayKey) {
                $this->assertArrayHasKey(
                    $arrayKey,
                    $result[self::ITEMS_FIELD][$identifier]
                );
            }
        }
    }

    /**
     * @param int $productId
     * @return string
     */
    protected function getIdentifier($productId)
    {
        return sprintf('%s%s', self::IDENTIFIER_PREFIX, $productId);
    }
}
