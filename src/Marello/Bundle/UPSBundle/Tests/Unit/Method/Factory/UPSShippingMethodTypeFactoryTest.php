<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Method\Factory;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Provider\UPSTransport;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Cache\ShippingPriceCache;
use Marello\Bundle\UPSBundle\Factory\PriceRequestFactory;
use Marello\Bundle\UPSBundle\Method\UPSShippingMethodType;
use Marello\Bundle\UPSBundle\Method\Factory\UPSShippingMethodTypeFactory;
use Marello\Bundle\UPSBundle\Method\Identifier\UPSMethodTypeIdentifierGeneratorInterface;

class UPSShippingMethodTypeFactoryTest extends TestCase
{
    /**
     * @var UPSMethodTypeIdentifierGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $typeIdentifierGenerator;

    /**
     * @var IntegrationIdentifierGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $methodIdentifierGenerator;

    /**
     * @var UPSTransport|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transport;

    /**
     * @var PriceRequestFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $priceRequestFactory;

    /**
     * @var ShippingPriceCache|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shippingPriceCache;

    /**
     * @var UPSShippingMethodTypeFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $factory;

    protected function setUp(): void
    {
        $this->typeIdentifierGenerator = $this->createMock(UPSMethodTypeIdentifierGeneratorInterface::class);
        $this->methodIdentifierGenerator = $this->createMock(IntegrationIdentifierGeneratorInterface::class);
        $this->transport = $this->createMock(UPSTransport::class);
        $this->priceRequestFactory = $this->createMock(PriceRequestFactory::class);
        $this->shippingPriceCache = $this->createMock(ShippingPriceCache::class);

        $this->factory = new UPSShippingMethodTypeFactory(
            $this->typeIdentifierGenerator,
            $this->methodIdentifierGenerator,
            $this->transport,
            $this->priceRequestFactory,
            $this->shippingPriceCache
        );
    }

    public function testCreate()
    {
        $identifier = 'ups_1_59';
        $methodId = 'ups_1';

        /** @var UPSSettings|\PHPUnit\Framework\MockObject\MockObject $settings */
        $settings = $this->createMock(UPSSettings::class);

        /** @var Channel|\PHPUnit\Framework\MockObject\MockObject $channel */
        $channel = $this->createMock(Channel::class);
        $channel->expects($this->any())
            ->method('getTransport')
            ->willReturn($settings);

        /** @var ShippingService|\PHPUnit\Framework\MockObject\MockObject $service */
        $service = $this->createMock(ShippingService::class);

        $service->expects($this->once())
            ->method('getDescription')
            ->willReturn('air');

        $this->methodIdentifierGenerator->expects($this->once())
            ->method('generateIdentifier')
            ->with($channel)
            ->willReturn($methodId);

        $this->typeIdentifierGenerator->expects($this->once())
            ->method('generateIdentifier')
            ->with($channel, $service)
            ->willReturn($identifier);

        $this->assertEquals(new UPSShippingMethodType(
            $identifier,
            'air',
            $methodId,
            $service,
            $settings,
            $this->transport,
            $this->priceRequestFactory,
            $this->shippingPriceCache
        ), $this->factory->create($channel, $service));
    }
}
