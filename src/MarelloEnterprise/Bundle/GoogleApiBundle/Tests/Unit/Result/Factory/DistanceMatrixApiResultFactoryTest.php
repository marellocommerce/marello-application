<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Tests\Unit\Result\Factory;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\DistanceMatrixApiResultFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResult;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResultInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Psr\Log\LoggerInterface;

class DistanceMatrixApiResultFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var DistanceMatrixApiResultFactory
     */
    protected $distanceMatrixApiResultFactory;

    protected function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->distanceMatrixApiResultFactory = new DistanceMatrixApiResultFactory($this->logger);
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
        $context
            ->expects(static::any())
            ->method('getDestinationAddress')
            ->willReturn($address);

        $actualResult = $this->distanceMatrixApiResultFactory->createResult($restResponse, $context);

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
            $this->distanceMatrixApiResultFactory->createExceptionResult(new RestException($message))
        );
    }

    /**
     * @return array
     */
    public function getApiResponse()
    {
        return [
            'noErrors' => [
                'response' => $this->createSuccessResponse(1000, 360),
                'expectedResult' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => true,
                    GoogleApiResult::FIELD_RESULT => [
                        DistanceMatrixApiResultFactory::DISTANCE => 1000,
                        DistanceMatrixApiResultFactory::DURATION => 360,
                    ]
                ])
            ],
            'zeroResults' => [
                'response' => $this->createWarningResponse(DistanceMatrixApiResultFactory::ZERO_RESULTS_CODE),
                'expectedResult' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => false,
                    GoogleApiResult::FIELD_ERROR_TYPE => GoogleApiResult::WARNING_TYPE,
                    GoogleApiResult::FIELD_ERROR_MESSAGE =>
                        'Google Maps Distance Matrix API can\'t calculate distance between address and address',
                    GoogleApiResult::FIELD_ERROR_CODE => DistanceMatrixApiResultFactory::ZERO_RESULTS_CODE
                ])
            ],
            'overQueryLimit' => [
                'response' => $this->createFaultResponse(
                    DistanceMatrixApiResultFactory::OVER_QUERY_LIMIT_CODE,
                    'Over query limit'
                ),
                'expectedResult' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => false,
                    GoogleApiResult::FIELD_ERROR_TYPE => GoogleApiResult::ERROR_TYPE,
                    GoogleApiResult::FIELD_ERROR_MESSAGE => 'Over query limit',
                    GoogleApiResult::FIELD_ERROR_CODE => DistanceMatrixApiResultFactory::OVER_QUERY_LIMIT_CODE
                ])
            ],
            'otherError' => [
                'response' => $this->createFaultResponse(DistanceMatrixApiResultFactory::INVALID_REQUEST_CODE),
                'expectedResult' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => false,
                    GoogleApiResult::FIELD_ERROR_TYPE => GoogleApiResult::ERROR_TYPE,
                    GoogleApiResult::FIELD_ERROR_MESSAGE => 'Other error',
                    GoogleApiResult::FIELD_ERROR_CODE => DistanceMatrixApiResultFactory::INVALID_REQUEST_CODE
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
            'rows' => [],
            'status' => $code,
        ];

        if ($message) {
            $response['error_message'] = $message;
        }

        return $response;
    }

    /**
     * @param string $code
     *
     * @return array
     */
    private function createWarningResponse($code)
    {
        return [
            'rows' => [
                [
                    'elements' => [
                        [
                            'status' => $code,
                        ]
                    ]
                ]
            ],
            'status' => 'OK',
        ];
    }

    /**
     * @param int $distance
     * @param int $duration
     * @return array
     */
    private function createSuccessResponse($distance, $duration)
    {
        return [
            'rows' => [
                [
                    'elements' => [
                        [
                            'status' => 'OK',
                            'distance' => [
                                'text' => (string)$distance,
                                'value' => $distance
                            ],
                            'duration' => [
                                'text' => (string)$duration,
                                'value' => $duration
                            ],
                        ]
                    ]
                ]
            ],
            'status' => 'OK',
        ];
    }
}
