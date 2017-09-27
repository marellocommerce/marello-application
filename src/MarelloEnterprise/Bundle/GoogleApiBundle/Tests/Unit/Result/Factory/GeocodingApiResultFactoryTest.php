<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Tests\Unit\Result\Factory;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GeocodingApiResultFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResult;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResultInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Psr\Log\LoggerInterface;

class GeocodingApiResultFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var GeocodingApiResultFactory
     */
    protected $geocodingApiResultFactory;

    protected function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->geocodingApiResultFactory = new GeocodingApiResultFactory($this->logger);
    }

    /**
     * @dataProvider getAPIResponse
     *
     * @param array $response
     * @param GoogleApiResultInterface $expectedResult
     */
    public function testCreateResult(array $response, GoogleApiResultInterface $expectedResult)
    {
        /** @var RestResponseInterface|\PHPUnit_Framework_MockObject_MockObject $restResponse */
        $restResponse = $this->createMock(RestResponseInterface::class);
        $restResponse->expects(static::once())
            ->method('json')
            ->willReturn($response);
        /** @var MarelloAddress|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->createMock(MarelloAddress::class);
        $address
            ->expects(static::any())
            ->method('__toString')
            ->willReturn('address');
        /** @var GoogleApiContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock(GoogleApiContextInterface::class);
        $context
            ->expects(static::any())
            ->method('getOriginAddress')
            ->willReturn($address);

        $actualResult = $this->geocodingApiResultFactory->createResult($restResponse, $context);

        static::assertEquals($expectedResult, $actualResult);
    }

    public function testCreateExceptionResult()
    {
        $message = 'error message';

        $expected = new GoogleApiResult([
                GoogleApiResult::FIELD_STATUS => false,
                GoogleApiResult::FIELD_ERROR_TYPE => GoogleApiResult::ERROR_TYPE,
                GoogleApiResult::FIELD_ERROR_MESSAGE => $message,
                GoogleApiResult::FIELD_ERROR_CODE => 0
        ]);

        static::assertEquals(
            $expected,
            $this->geocodingApiResultFactory->createExceptionResult(new RestException($message))
        );
    }

    /**
     * @return array
     */
    public function getApiResponse()
    {
        return [
            'noErrors' => [
                'response' => $this->createSuccessResponse(10, 10, 'address'),
                'expectedResult' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => true,
                    GoogleApiResult::FIELD_RESULT => [
                        GeocodingApiResultFactory::LATITUDE => 10,
                        GeocodingApiResultFactory::LONGITUDE => 10,
                        GeocodingApiResultFactory::FORMATTED_ADDRESS => 'address',
                    ]
                ])
            ],
            'zeroResults' => [
                'response' => $this->createFaultResponse(GeocodingApiResultFactory::ZERO_RESULTS_CODE),
                'expectedResult' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => false,
                    GoogleApiResult::FIELD_ERROR_TYPE => GoogleApiResult::WARNING_TYPE,
                    GoogleApiResult::FIELD_ERROR_MESSAGE =>
                        'Google Maps Geocoding API can\'t find coordinates for address',
                    GoogleApiResult::FIELD_ERROR_CODE => GeocodingApiResultFactory::ZERO_RESULTS_CODE
                ])
            ],
            'overQueryLimit' => [
                'response' => $this->createFaultResponse(
                    GeocodingApiResultFactory::OVER_QUERY_LIMIT_CODE,
                    'Over query limit'
                ),
                'expectedResult' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => false,
                    GoogleApiResult::FIELD_ERROR_TYPE => GoogleApiResult::ERROR_TYPE,
                    GoogleApiResult::FIELD_ERROR_MESSAGE => 'Over query limit',
                    GoogleApiResult::FIELD_ERROR_CODE => GeocodingApiResultFactory::OVER_QUERY_LIMIT_CODE
                ])
            ],
            'otherError' => [
                'response' => $this->createFaultResponse(GeocodingApiResultFactory::INVALID_REQUEST_CODE),
                'expectedResult' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => false,
                    GoogleApiResult::FIELD_ERROR_TYPE => GoogleApiResult::ERROR_TYPE,
                    GoogleApiResult::FIELD_ERROR_MESSAGE => 'Other error',
                    GoogleApiResult::FIELD_ERROR_CODE => GeocodingApiResultFactory::INVALID_REQUEST_CODE
                ])
            ],
        ];
    }

    /**
     * @param string $code
     * @param string|null $message
     *
     * @return array
     */
    private function createFaultResponse($code, $message = null)
    {
        $response =  [
            'results' => [],
            'status' => $code,
        ];

        if ($message) {
            $response['error_message'] = $message;
        }

        return $response;
    }

    /**
     * @param float $lat
     * @param float $lng
     * @param string $formattedAddress
     * @return array
     */
    private function createSuccessResponse($lat, $lng, $formattedAddress)
    {
        return [
            'results' => [
                [
                    'formatted_address' => $formattedAddress,
                    'geometry' => [
                        'location' => [
                            'lat' => $lat,
                            'lng' => $lng
                        ]
                    ]
                ]
            ],
            'status' => 'OK',
        ];
    }
}
