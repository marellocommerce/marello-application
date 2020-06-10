<?php

namespace MarelloOroCommerce\src\Marello\Bundle\OroCommerceBundle\Tests\Functional\Controller;

use Marello\Bundle\OroCommerceBundle\Client\Factory\OroCommerceRestClientFactory;
use Marello\Bundle\OroCommerceBundle\Client\OroCommerceRestClient;
use Marello\Bundle\OroCommerceBundle\Controller\AjaxOroCommerceController;
use Marello\Bundle\OroCommerceBundle\Tests\Functional\DataFixtures\LoadAdditionalSalesData;
use Marello\Bundle\OroCommerceBundle\Tests\Functional\DataFixtures\LoadChannelData;
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
        $this->loadFixtures([
            LoadChannelData::class
        ]);
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
                    'salesChannelGroup'
                        => $this->getReference(LoadAdditionalSalesData::TEST_SALESCHANNELGROUP_OROCOMMERCE),
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
        if ($expectedResult === true) {
            $mockRestClient
                ->expects(static::any())
                ->method('getJson')
                ->willReturn([]);
        } else {
            $mockRestClient
                ->expects(static::any())
                ->method('getJson')
                ->willThrowException(new RestException('Authorization Failed'));
        }
        $mockRestClientFactory = $this->createMock(OroCommerceRestClientFactory::class);
        $mockRestClientFactory
            ->expects(static::any())
            ->method('createRestClient')
            ->willReturn($mockRestClient);
        $container = $this->getContainer();
        $cache = $container->get('marello_orocommerce.cache');
        $cacheKeyGenerator = $container->get('marello_orocommerce.cache_key_generator');
        $transport = $container->get('marello_orocommerce.integration.transport');
        $transport->setRestClientFactory($mockRestClientFactory);
        $translator = $container->get('translator.default');
        $ajaxOroCommerceController = new AjaxOroCommerceController($cache, $cacheKeyGenerator, $transport, $translator);
        $ajaxOroCommerceController->setContainer($container);
        

        $this->client->request(
            'POST',
            $this->getUrl(
                'marello_orocommerce_validate_connection',
                ['channelId' => $this->getReference('orocommerce_channel:first_test_channel')]
            ),
            $request
        );
        $response = $ajaxOroCommerceController->validateConnectionAction(
            $this->client->getRequest(),
            $this->getReference('orocommerce_channel:first_test_channel')
        );
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertEquals($expectedResult, $result['success']);
        $this->assertEquals($message, $result['message']);
    }

    abstract public function dataProvider();
}
