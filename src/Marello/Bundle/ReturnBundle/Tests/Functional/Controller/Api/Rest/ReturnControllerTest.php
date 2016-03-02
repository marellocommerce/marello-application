<?php

namespace Marello\Bundle\ReturnBundle\Tests\Functional\Controller\Api\Rest;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadReturnData;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolation
 */
class ReturnControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateWsseAuthHeader()
        );

        $this->loadFixtures([
            LoadReturnData::class,
        ]);
    }

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_return_api_get_returns')
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        $this->assertCount(10, json_decode($response->getContent(), true));
    }

    public function testGet()
    {
        $testedReturn = $this->getReference('marello_return_1');

        $this->client->request(
            'GET',
            $this->getUrl('marello_return_api_get_return', ['id' => $testedReturn->getId()])
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        $result = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('returnNumber', $result);
        $this->assertArrayHasKey('returnItems', $result);

        $this->assertEquals($testedReturn->getId(), $result['id']);
        $this->assertEquals($testedReturn->getReturnNumber(), $result['returnNumber']);

        $this->assertCount($testedReturn->getReturnItems()->count(), $result['returnItems']);
    }

    public function testCreate()
    {
        /** @var Order $returnedOrder */
        $returnedOrder = $this->getReference('marello_order_unreturned');

        $data = [
            'order'       => $returnedOrder->getOrderNumber(),
            'returnItems' => $returnedOrder->getItems()->map(function (OrderItem $item) {
                return [
                    'orderItem' => $item->getId(),
                    'quantity'  => $item->getQuantity(),
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
    }
}
