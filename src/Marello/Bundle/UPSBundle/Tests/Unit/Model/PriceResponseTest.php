<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;

use Marello\Bundle\UPSBundle\Model\Response\PriceResponse;

class PriceResponseTest extends TestCase
{
    /**
     * @var PriceResponse
     */
    protected $priceResponse;

    public function setUp(): void
    {
        $this->priceResponse = new PriceResponse();
    }

    public function testParseResponseSinglePrice()
    {
        $restResponse = $this->createMock(RestResponseInterface::class);
        $restResponse
            ->expects(static::once())
            ->method('json')
            ->willReturn([
                'RateResponse' => [
                    'RatedShipment' => [
                        'Service' => [
                            'Code' => '02'
                        ],
                        'TotalCharges' => [
                            'CurrencyCode' => 'USD',
                            'MonetaryValue' => '8.60'
                        ]
                    ]
                ]
            ]);
        $this->priceResponse->parse($restResponse);
        $expected = [
            '02' => Price::create('8.60', 'USD'),
        ];
        static::assertEquals($expected, $this->priceResponse->getPricesByServices());
    }

    public function testParseResponseMultiplePrices()
    {
        $restResponse = $this->createMock(RestResponseInterface::class);
        $restResponse
            ->expects(static::once())
            ->method('json')
            ->willReturn([
                'RateResponse' => [
                    'RatedShipment' => [
                        [
                            'Service' => [
                                'Code' => '02'
                            ],
                            'TotalCharges' => [
                                'CurrencyCode' => 'USD',
                                'MonetaryValue' => '8.60'
                            ]
                        ],
                        [
                            'Service' => [
                                'Code' => '12'
                            ],
                            'TotalCharges' => [
                                'CurrencyCode' => 'USD',
                                'MonetaryValue' => '18.60'
                            ]
                        ],
                    ]
                ]
            ]);
        $this->priceResponse->parse($restResponse);

        $pricesExpected = [
            '02' => Price::create('8.60', 'USD'),
            '12' => Price::create('18.60', 'USD'),
        ];

        static::assertEquals($pricesExpected, $this->priceResponse->getPricesByServices());
        static::assertEquals($pricesExpected['02'], $this->priceResponse->getPriceByService('02'));
        static::assertEquals($pricesExpected['12'], $this->priceResponse->getPriceByService('12'));
    }

    public function testParseEmptyResponse()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No price data in provided string');
        $restResponse = $this->createMock(RestResponseInterface::class);
        $restResponse
            ->expects(static::once())
            ->method('json')
            ->willReturn([]);
        $this->priceResponse->parse($restResponse);
    }

    public function testGetPriceByServicesException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Response data not loaded');
        $this->priceResponse->getPricesByServices();
    }

    public function testGetPriceByService()
    {
        static::assertNull($this->priceResponse->getPriceByService('fakeService'));
    }
}
