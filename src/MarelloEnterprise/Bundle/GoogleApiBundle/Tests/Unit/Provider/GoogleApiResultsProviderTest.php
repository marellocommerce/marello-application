<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Tests\Unit\Provider;

use MarelloEnterprise\Bundle\GoogleApiBundle\Request\Factory\GoogleApiRequestFactoryInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Request\GoogleApiRequestInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GoogleApiResultFactoryInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Client\Factory\GoogleApiClientFactoryInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Provider\GoogleApiResultsProvider;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResultInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;

class GoogleApiResultsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GoogleApiRequestFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestFactory;

    /**
     * @var GoogleApiResultFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var GoogleApiClientFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactory;

    /**
     * @var GoogleApiResultsProvider
     */
    protected $googleApiResultsProvider;

    protected function setUp()
    {
        $this->requestFactory = $this->createMock(GoogleApiRequestFactoryInterface::class);
        $this->resultFactory = $this->createMock(GoogleApiResultFactoryInterface::class);
        $this->clientFactory = $this->createMock(GoogleApiClientFactoryInterface::class);
        $this->googleApiResultsProvider = new GoogleApiResultsProvider(
            $this->requestFactory,
            $this->resultFactory,
            $this->clientFactory
        );
    }

    /**
     * @dataProvider getApiResultsDataProvider
     *
     * @param array $requestParams
     * @param int $callGetTimes
     * @param int $callCreateResultTimes
     */
    public function testGetApiResults(array $requestParams, $callGetTimes, $callCreateResultTimes)
    {
        $client = $this->createMock(RestClientInterface::class);
        $response = $this->createMock(RestResponseInterface::class);
        $client
            ->expects(static::exactly($callGetTimes))
            ->method('get')
            ->with(GoogleApiResultsProvider::FORMAT, $requestParams)
            ->willReturn($response);
        $request = $this->createMock(GoogleApiRequestInterface::class);
        $request
            ->expects(static::once())
            ->method('getRequestParameters')
            ->willReturn($requestParams);
        $result = $this->createMock(GoogleApiResultInterface::class);
        $this->clientFactory
            ->expects(static::once())
            ->method('createClient')
            ->willReturn($client);
        $this->requestFactory
            ->expects(static::once())
            ->method('createRequest')
            ->willReturn($request);
        /** @var GoogleApiContextInterface|\PHPUnit_Framework_MockObject_MockObject $context **/
        $context = $this->createMock(GoogleApiContextInterface::class);
        $this->resultFactory
            ->expects(static::exactly($callCreateResultTimes))
            ->method('createResult')
            ->with($response, $context)
            ->willReturn($result);

        $this->googleApiResultsProvider->getApiResults($context);
    }

    /**
     * @return array
     */
    public function getApiResultsDataProvider()
    {
        return [
            [
                'requestParams' => [],
                'callGetTimes' => 0,
                'callCreateResultTimes' => 0
            ],
            [
                'requestParams' => ['parameter' => 'parameter'],
                'callGetTimes' => 1,
                'callCreateResultTimes' => 1
            ]
        ];
    }
}
