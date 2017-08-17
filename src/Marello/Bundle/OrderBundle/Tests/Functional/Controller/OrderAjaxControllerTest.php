<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Controller;

use Marello\Bundle\OrderBundle\Form\Type\OrderType;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class OrderAjaxControllerTest extends WebTestCase
{
    const ITEMS_FIELD = 'items';
    const IDENTIFIER_PREFIX = 'product-id-';

    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadOrderData::class,
        ]);
    }

    public function testFormChangesAction()
    {
        $orderItemKeys = ['price', 'tax_code'];
        $productIds = [
            $this->getReference(LoadProductData::PRODUCT_1_REF)->getId(),
            $this->getReference(LoadProductData::PRODUCT_2_REF)->getId(),
            $this->getReference(LoadProductData::PRODUCT_3_REF)->getId()
        ];
        $this->client->request(
            'POST',
            $this->getUrl('marello_order_form_changes'),
            [
                OrderType::NAME => [
                    'salesChannel' => $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId(),
                    'items' => [
                        ['product' => $productIds[0], 'quantity' => 1],
                        ['product' => $productIds[1], 'quantity' => 2],
                        ['product' => $productIds[2], 'quantity' => 1],
                    ]
                ]
            ]
        );

        $response = $this->client->getResponse();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertArrayHasKey(self::ITEMS_FIELD, $result);
        $this->assertCount(count($productIds), $result[self::ITEMS_FIELD]);
        foreach ($productIds as $id) {
            $this->assertArrayHasKey($this->getIdentifier($id), $result[self::ITEMS_FIELD]);
            foreach ($orderItemKeys as $key) {
                $this->assertArrayHasKey(
                    $key,
                    $result[self::ITEMS_FIELD][$this->getIdentifier($id)]
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
