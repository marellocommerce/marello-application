<?php

namespace Marello\Bundle\ReturnBundle\Tests\Functional\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ReturnBundle\Tests\Functional\DataFixtures\LoadReturnData;

class ReturnControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateWsseAuthHeader()
        );

        $this->loadFixtures([
            LoadReturnData::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testCreateNewReturn()
    {
        /** @var Order $returnedOrder */
        $returnedOrder = $this->getReference('marello_order_unreturned');

        $data = [
            'order'       => $returnedOrder->getOrderNumber(),
            'salesChannel' => $returnedOrder->getSalesChannel()->getCode(),
            'returnReference' => uniqid() . 'TEST',
            'returnItems' => $returnedOrder->getItems()->map(function (OrderItem $item) {
                return [
                    'orderItem' => $item->getId(),
                    'quantity'  => 1,
                    'reason'    => 'damaged',
                ];
            })->toArray(),
        ];

        $this->client->request(
            'POST',
            $this->getUrl('marello_return_api_post_return'),
            $data
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_CREATED);

        $this->assertArrayHasKey('id', $response);

        /** @var ReturnEntity $return */
        $return = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository('MarelloReturnBundle:ReturnEntity')
            ->findOneBy($response);

        $this->assertEquals(
            $returnedOrder->getId(),
            $return->getOrder()->getId(),
            'Created return should have correct order assigned.'
        );

        $orderedItemIds = $returnedOrder->getItems()->map(function (OrderItem $orderItem) {
            return $orderItem->getId();
        });

        $returnedItemIds = $return->getReturnItems()->map(function (ReturnItem $returnItem) {
            return $returnItem->getOrderItem()->getId();
        });

        $this->assertEquals(count($orderedItemIds), count($returnedItemIds));
        $this->assertEquals($orderedItemIds->toArray(), $returnedItemIds->toArray());

        $return->getReturnItems()->map(function (ReturnItem $returnItem) {
            $this->assertEquals(
                $returnItem->getOrderItem()->getQuantity(),
                $returnItem->getQuantity()
            );
        });

        return $response;
    }

    /**
     * {@inheritdoc}
     * @param $returnCreatedResponse
     * @depends testCreateNewReturn
     */
    public function testGetReturnById($returnCreatedResponse)
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_return_api_get_return', ['id' => $returnCreatedResponse['id']])
        );

        $response = $this->client->getResponse();
        $this->hasArrayKeysInResponse($response);
        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     */
    public function testGetReturnList()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_return_api_get_returns')
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertCount(10, json_decode($response->getContent(), true));
    }

    /**
     * Test return not found
     */
    public function testGetNotFound()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_return_api_get_return', ['id' => 0])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    /**
     * Test if response has the correct fields for Order API repsonse
     * @param Response $response
     */
    protected function hasArrayKeysInResponse($response)
    {
        $jsonDecoded = json_decode($response->getContent(), true);
        foreach ($this->getFields() as $index => $fields) {
            foreach (array_keys($fields) as $field) {
                $this->assertArrayHasKey($field, $jsonDecoded);
            }
        }
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getFields()
    {
        $itemConfig = [
            'fields'           => [
                'productName' => [],
                'productSku'  => [],
                'quantity'    => [],
                'price'       => [],
                'tax'         => [],
                'totalPrice'  => [],
            ],
        ];

        $config = [
            'fields'           => [
                'id'           => [],
                'returnNumber' => [],
                'returnReference' => [],
                'returnItems'  => [
                    'fields'           => [
                        'id'        => [],
                        'quantity'  => [],
                        'orderItem' => $itemConfig,
                        'createdAt' => [],
                        'updatedAt' => [],
                    ],
                ],
                'workflowItems'   => [],
            ],
        ];

        return $config;
    }
}
