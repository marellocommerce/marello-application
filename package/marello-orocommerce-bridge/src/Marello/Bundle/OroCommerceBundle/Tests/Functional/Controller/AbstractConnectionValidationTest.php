<?php

namespace MarelloOroCommerce\src\Marello\Bundle\OroCommerceBundle\Tests\Functional\Controller;

use Marello\Bundle\OroCommerceBundle\Client\Factory\OroCommerceRestClientFactory;
use Marello\Bundle\OroCommerceBundle\Client\OroCommerceRestClient;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

abstract class AbstractConnectionValidationTest extends WebTestCase
{
    /**
     * @var OroCommerceRestClientFactory
     */
    protected $realRestClientFactory;
    
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
    }

    /** {@inheritdoc} */
    public function tearDown()
    {
        $this->getContainer()->set('marello_orocommerce.rest.client_factory', $this->realRestClientFactory);
        parent::tearDown();
    }

    /**
     * @dataProvider dataProvider
     * @param string $user
     * @param bool $expectedResult
     * @param string $message
     */
    public function testValidateConnectionAction($user, $expectedResult, $message)
    {
        $request = [
            'oro_integration_channel_form' => [
                'type' => 'orocommerce',
                'name' => 'OroCommerce',
                'transportType' => 'orocommerce',
                'transport' => [
                    'url' => 'http://orocommerce.com/admin',
                    'username' => $user,
                    'key' => 'qwerty',
                    'currency' => 'USD',
                    'inventoryThreshold' => 1,
                    'lowInventoryThreshold' => 1,
                    'backOrder' => 1,
                    'productUnit' => 'each',
                    'customerTaxCode' => 1,
                    'priceList' => 1,
                    'productFamily' => 1
                ],
                'defaultUserOwner' => 1,
                '_token' => 'fJIn_stVOYze29j5h2WW-dkyoH7P8kgB5v3oiZ_koPs'
            ]
        ];
        $mockRestClient = $this->createMock(OroCommerceRestClient::class);
        if ($expectedResult) {
            $mockRestClient
                ->expects(static::once())
                ->method('get')
                ->willReturn([]);
        } else {
            $mockRestClient
                ->expects(static::once())
                ->method('get')
                ->willThrowException(new RestException('Authorization Failed'));
        }
        $mockRestClientFactory = $this->createMock(OroCommerceRestClientFactory::class);
        $mockRestClientFactory
            ->expects(static::any())
            ->method('createRestClient')
            ->willReturn($mockRestClient);
        
        $this->realRestClientFactory = $this->getContainer()->get('marello_orocommerce.rest.client_factory');
        $this->client->getContainer()->set('marello_orocommerce.rest.client_factory', $mockRestClientFactory);

        $this->client->request(
            'POST',
            $this->getUrl('marello_orocommerce_validate_connection', ['channelId' => 0]),
            $request
        );
        $response = $this->client->getResponse();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertEquals($expectedResult, $result['success']);
        $this->assertEquals($message, $result['message']);
    }

    abstract public function dataProvider();
}
