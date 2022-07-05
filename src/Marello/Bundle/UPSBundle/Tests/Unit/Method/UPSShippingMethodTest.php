<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Method;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\AddressBundle\Entity\Country;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Method\UPSShippingMethod;
use Marello\Bundle\UPSBundle\Cache\ShippingPriceCache;
use Marello\Bundle\UPSBundle\Model\Request\PriceRequest;
use Marello\Bundle\UPSBundle\Cache\ShippingPriceCacheKey;
use Marello\Bundle\UPSBundle\Factory\PriceRequestFactory;
use Marello\Bundle\UPSBundle\Model\Response\PriceResponse;
use Marello\Bundle\UPSBundle\Method\UPSShippingMethodType;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\UPSBundle\Form\Type\UPSShippingMethodOptionsType;
use Marello\Bundle\UPSBundle\Provider\UPSTransport as UPSTransportProvider;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class UPSShippingMethodTest extends TestCase
{
    use EntityTrait;

    /**
     * @internal
     */
    const IDENTIFIER = 'ups_1';

    /**
     * @internal
     */
    const LABEL = 'ups_label';

    /**
     * @internal
     */
    const TYPE_IDENTIFIER = '59';

    /**
     * @internal
     */
    const ICON = 'bundles/icon-uri.png';

    /**
     * @var UPSTransportProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $transportProvider;

    /**
     * @var UPSSettings|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $transport;

    /**
     * @var PriceRequestFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceRequestFactory;

    /**
     * @var UPSShippingMethod
     */
    protected $upsShippingMethod;

    /**
     * @var ShippingPriceCache|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cache;

    protected function setUp(): void
    {
        $this->transportProvider = $this->createMock(UPSTransportProvider::class);

        $shippingService = $this->getEntity(
            ShippingService::class,
            ['id' => 1, 'code' => 'ups_identifier', 'description' => 'ups_label', 'country' => new Country('US')]
        );

        /** @var PriceRequestFactory | \PHPUnit\Framework\MockObject\MockObject $priceRequestFactory */
        $this->priceRequestFactory = $this->createMock(PriceRequestFactory::class);

        $this->transport = $this->createMock(UPSSettings::class);
        $this->transport->expects(self::any())->method('getApplicableShippingServices')->willReturn([$shippingService]);

        $this->cache = $this->createMock(ShippingPriceCache::class);

        $type = $this->createMock(UPSShippingMethodType::class);
        $type
            ->method('getIdentifier')
            ->willReturn(self::TYPE_IDENTIFIER);

        $this->upsShippingMethod =
            new UPSShippingMethod(
                self::IDENTIFIER,
                self::LABEL,
                self::ICON,
                [$type],
                $this->transport,
                $this->transportProvider,
                $this->priceRequestFactory,
                $this->cache,
                true
            );
    }

    public function testIsGrouped()
    {
        static::assertTrue($this->upsShippingMethod->isGrouped());
    }

    public function testIsEnabled()
    {
        static::assertTrue($this->upsShippingMethod->isEnabled());
    }

    public function testGetIdentifier()
    {
        static::assertEquals(self::IDENTIFIER, $this->upsShippingMethod->getIdentifier());
    }

    public function testGetLabel()
    {
        static::assertEquals(self::LABEL, $this->upsShippingMethod->getLabel());
    }

    public function testGetTypes()
    {
        $types = $this->upsShippingMethod->getTypes();

        static::assertCount(1, $types);
        static::assertEquals(self::TYPE_IDENTIFIER, $types[0]->getIdentifier());
    }

    public function testGetType()
    {
        $identifier = self::TYPE_IDENTIFIER;
        $type = $this->upsShippingMethod->getType($identifier);

        static::assertInstanceOf(UPSShippingMethodType::class, $type);
        static::assertEquals(self::TYPE_IDENTIFIER, $type->getIdentifier());
    }

    public function testGetOptionsConfigurationFormType()
    {
        $type = $this->upsShippingMethod->getOptionsConfigurationFormType();

        static::assertEquals(UPSShippingMethodOptionsType::class, $type);
    }

    public function testGetSortOrder()
    {
        static::assertEquals('20', $this->upsShippingMethod->getSortOrder());
    }


    /**
     * @param int $methodSurcharge
     * @param int $typeSurcharge
     * @param int $expectedPrice
     *
     * @dataProvider calculatePricesDataProvider
     */
    public function testCalculatePrices($methodSurcharge, $typeSurcharge, $expectedPrice)
    {
        /** @var ShippingContextInterface|\PHPUnit\Framework\MockObject\MockObject $context */
        $context = $this->createMock(ShippingContextInterface::class);

        $methodOptions = ['surcharge' => $methodSurcharge];
        $optionsByTypes = ['01' => ['surcharge' => $typeSurcharge]];

        /** @var PriceRequest|\PHPUnit\Framework\MockObject\MockObject $priceRequest */
        $priceRequest = $this->createMock(PriceRequest::class);

        $this->priceRequestFactory->expects(self::once())->method('create')->willReturn($priceRequest);

        $this->transportProvider->expects(self::never())->method('getPriceResponse');

        $cacheKey = (new ShippingPriceCacheKey())->setTransport($this->transport)->setPriceRequest($priceRequest)
            ->setMethodId($this->upsShippingMethod->getIdentifier())->setTypeId(null);

        $this->cache->expects(static::once())
            ->method('createKey')
            ->with($this->transport, $priceRequest, $this->upsShippingMethod->getIdentifier(), null)
            ->willReturn($cacheKey);

        $this->cache->expects(static::once())
            ->method('containsPrice')
            ->with($cacheKey)
            ->willReturn(true);

        $this->cache->expects(static::once())
            ->method('fetchPrice')
            ->with($cacheKey)
            ->willReturn(Price::create(50, 'USD'));

        $prices = $this->upsShippingMethod->calculatePrices($context, $methodOptions, $optionsByTypes);

        static::assertCount(1, $prices);
        static::assertTrue(array_key_exists('01', $prices));
        static::assertEquals(Price::create($expectedPrice, 'USD'), $prices['01']);
    }

    /**
     * @return array
     */
    public function calculatePricesDataProvider()
    {
        return [
            'TypeSurcharge' => [
                'methodSurcharge' => 0,
                'typeSurcharge' => 5,
                'expectedPrice' => 55
            ],
            'MethodSurcharge' => [
                'methodSurcharge' => 3,
                'typeSurcharge' => 0,
                'expectedPrice' => 53
            ],
            'Method&TypeSurcharge' => [
                'methodSurcharge' => 3,
                'typeSurcharge' => 5,
                'expectedPrice' => 58
            ],
            'NoSurcharge' => [
                'methodSurcharge' => 0,
                'typeSurcharge' => 0,
                'expectedPrice' => 50
            ]
        ];
    }

    /**
     * @param string      $number
     * @param string|null $resultURL
     *
     * @dataProvider trackingDataProvider
     */
    public function testGetTrackingLink($number, $resultURL)
    {
        static::assertEquals($resultURL, $this->upsShippingMethod->getTrackingLink($number));
    }

    /**
     * @return array
     */
    public function trackingDataProvider()
    {
        return [
            'emptyTrackingNumber' => [
                'number' => '',
                'resultURL' => null,
            ],
            'wrongTrackingNumber2' => [
                'number' => '123123123123',
                'resultURL' => null,
            ],
            'rightTrackingNumber' => [
                'number' => '1Z111E111111111111',
                'resultURL' => UPSShippingMethod::TRACKING_URL . '1Z111E111111111111',
            ],
        ];
    }

    public function testCalculatePricesWithoutCache()
    {
        /** @var ShippingContextInterface|\PHPUnit\Framework\MockObject\MockObject $context */
        $context = $this->createMock(ShippingContextInterface::class);

        $methodOptions = ['surcharge' => 10];

        /** @var PriceRequest|\PHPUnit\Framework\MockObject\MockObject $priceRequest */
        $priceRequest = $this->createMock(PriceRequest::class);

        $cacheKey = (new ShippingPriceCacheKey())->setTransport($this->transport)->setPriceRequest($priceRequest)
            ->setMethodId($this->upsShippingMethod->getIdentifier());

        $this->prepareCache($cacheKey, $priceRequest);

        $this->cache->expects(static::exactly(4))
            ->method('containsPrice')
            ->withConsecutive(
                [$cacheKey->setTypeId('01')],
                [$cacheKey->setTypeId('02')],
                [$cacheKey->setTypeId('03')],
                [$cacheKey->setTypeId('04')]
            )
            ->willReturnOnConsecutiveCalls(true, true, false, false);

        $this->cache->expects(static::exactly(2))
            ->method('fetchPrice')
            ->withConsecutive(
                [$cacheKey],
                [$cacheKey]
            )
            ->willReturnOnConsecutiveCalls(Price::create(60, 'USD'), Price::create(70, 'USD'));

        $priceResponse = $this->createMock(PriceResponse::class);
        $priceResponse->expects(self::exactly(2))
            ->method('getPriceByService')
            ->withConsecutive(
                ['03'],
                ['04']
            )
            ->willReturnOnConsecutiveCalls(Price::create(80, 'USD'), Price::create(90, 'USD'));

        $this->transportProvider->expects(self::once())->method('getPriceResponse')->willReturn($priceResponse);

        $this->cache->expects(static::exactly(2))
            ->method('savePrice')
            ->withConsecutive(
                [$cacheKey->setTypeId('03')],
                [$cacheKey->setTypeId('03')]
            )
            ->willReturn(Price::create(80, 'USD'), Price::create(90, 'USD'));

        $optionsByTypes = [
            '01' => ['surcharge' => 20],
            '02' => ['surcharge' => 30],
            '03' => ['surcharge' => 40],
            '04' => ['surcharge' => 50],
        ];

        $prices = $this->upsShippingMethod->calculatePrices($context, $methodOptions, $optionsByTypes);

        static::assertEquals([
            '01' => Price::create(90, 'USD'),
            '02' => Price::create(110, 'USD'),
            '03' => Price::create(130, 'USD'),
            '04' => Price::create(150, 'USD'),
        ], $prices);
    }

    public function testCalculatePricesOneWithoutCache()
    {
        /** @var ShippingContextInterface|\PHPUnit\Framework\MockObject\MockObject $context */
        $context = $this->createMock(ShippingContextInterface::class);

        $methodOptions = ['surcharge' => 10];

        /** @var PriceRequest|\PHPUnit\Framework\MockObject\MockObject $priceRequest */
        $priceRequest = $this->createMock(PriceRequest::class);

        $cacheKey = (new ShippingPriceCacheKey())->setTransport($this->transport)->setPriceRequest($priceRequest)
            ->setMethodId($this->upsShippingMethod->getIdentifier());

        $this->prepareCache($cacheKey, $priceRequest);

        $this->cache->expects(static::exactly(2))
            ->method('containsPrice')
            ->withConsecutive(
                [$cacheKey->setTypeId('01')],
                [$cacheKey->setTypeId(self::TYPE_IDENTIFIER)]
            )
            ->willReturnOnConsecutiveCalls(true, false);

        $this->cache->expects(static::once())
            ->method('fetchPrice')
            ->with($cacheKey)
            ->willReturn(Price::create(60, 'USD'));

        $priceResponse = $this->createMock(PriceResponse::class);
        $priceResponse->expects(self::once())
            ->method('getPriceByService')
            ->with(self::TYPE_IDENTIFIER)
            ->willReturn(Price::create(70, 'USD'));

        $this->transport = $this->createMock(UPSSettings::class);
        $service = $this->getEntity(ShippingService::class, [
            'code' => self::TYPE_IDENTIFIER,
            'description' => 'Air',
        ]);

        $priceRequest->expects(self::once())
            ->method('setServiceCode')
            ->with(self::TYPE_IDENTIFIER)
            ->willReturn($priceRequest);
        $priceRequest->expects(self::once())
            ->method('setServiceDescription')
            ->with('Air')
            ->willReturn($priceRequest);

        $this->transportProvider->expects(self::once())->method('getPriceResponse')->willReturn($priceResponse);

        $this->cache->expects(static::once())
            ->method('savePrice')
            ->with($cacheKey->setTypeId(self::TYPE_IDENTIFIER))
            ->willReturn(Price::create(70, 'USD'));

        $type = $this->createMock(UPSShippingMethodType::class);
        $type->method('getIdentifier')
            ->willReturn(self::TYPE_IDENTIFIER);
        $type->method('getShippingService')
            ->willReturn($service);

        $this->upsShippingMethod = new UPSShippingMethod(
            self::IDENTIFIER,
            self::LABEL,
            self::ICON,
            [$type],
            $this->transport,
            $this->transportProvider,
            $this->priceRequestFactory,
            $this->cache,
            true
        );

        $optionsByTypes = [
            '01' => ['surcharge' => 20],
            self::TYPE_IDENTIFIER => ['surcharge' => 30],
        ];

        $prices = $this->upsShippingMethod->calculatePrices($context, $methodOptions, $optionsByTypes);

        static::assertEquals([
            '01' => Price::create(90, 'USD'),
            self::TYPE_IDENTIFIER => Price::create(110, 'USD'),
        ], $prices);
    }

    public function testGetIcon()
    {
        static::assertSame(self::ICON, $this->upsShippingMethod->getIcon());
    }

    /**
     * @param ShippingPriceCacheKey $cacheKey
     * @param PriceRequest|\PHPUnit\Framework\MockObject\MockObject $priceRequest
     */
    protected function prepareCache(ShippingPriceCacheKey $cacheKey, $priceRequest)
    {
        $this->priceRequestFactory->expects(self::once())->method('create')->willReturn($priceRequest);

        $this->cache->expects(static::once())
            ->method('createKey')
            ->with($this->transport, $priceRequest, $this->upsShippingMethod->getIdentifier(), null)
            ->willReturn($cacheKey);
    }
}
