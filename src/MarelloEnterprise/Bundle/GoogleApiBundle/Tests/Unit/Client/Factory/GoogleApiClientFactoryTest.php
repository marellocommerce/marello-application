<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Tests\Unit\Client\Factory;

use MarelloEnterprise\Bundle\GoogleApiBundle\Client\Factory\GoogleApiClientFactory;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientFactoryInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;

class GoogleApiClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RestClientFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $restClientFactory;

    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $url = 'test_url';

    /**
     * @var GoogleApiClientFactory
     */
    protected $googleApiClientFactory;

    protected function setUp()
    {
        $this->restClientFactory = $this->createMock(RestClientFactoryInterface::class);
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->googleApiClientFactory = new GoogleApiClientFactory(
            $this->restClientFactory,
            $this->configManager,
            $this->url
        );
    }

    public function testCreateClient()
    {
        $expectedRestClient = $this->createMock(RestClientInterface::class);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with(GoogleApiClientFactory::GOOGLE_INTEGRATION_CLIENT_SECRET)
            ->willReturn(GoogleApiClientFactory::PARAM_API_KEY);

        $this->restClientFactory
            ->expects($this->once())
            ->method('createRestClient')
            ->with($this->url, [
                'query' => [
                    GoogleApiClientFactory::PARAM_API_KEY => GoogleApiClientFactory::PARAM_API_KEY
                ]
            ])
            ->willReturn($expectedRestClient);

        $actualResult = $this->googleApiClientFactory->createClient();

        $this->assertEquals($expectedRestClient, $actualResult);
    }
}
