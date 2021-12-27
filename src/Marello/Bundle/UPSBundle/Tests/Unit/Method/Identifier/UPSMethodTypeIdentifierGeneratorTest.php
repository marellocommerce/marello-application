<?php

namespace Marello\Bundle\UPSBundle\Tests\Units\Method\Identifier;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Method\Identifier\UPSMethodTypeIdentifierGenerator;

class UPSMethodTypeIdentifierGeneratorTest extends TestCase
{
    public function testGenerateIdentifier()
    {
        /** @var Channel|\PHPUnit\Framework\MockObject\MockObject $channel */
        $channel = $this->createMock(Channel::class);

        /** @var ShippingService|\PHPUnit\Framework\MockObject\MockObject $service */
        $service = $this->createMock(ShippingService::class);
        $service->expects($this->once())
            ->method('getCode')
            ->willReturn('59');


        $generator = new UPSMethodTypeIdentifierGenerator();

        $this->assertEquals('59', $generator->generateIdentifier($channel, $service));
    }
}
