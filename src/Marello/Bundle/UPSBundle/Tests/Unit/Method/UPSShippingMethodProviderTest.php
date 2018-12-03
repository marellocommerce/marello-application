<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Method;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\UPSBundle\Method\UPSShippingMethodProvider;
use Marello\Bundle\ShippingBundle\Method\Factory\IntegrationShippingMethodFactoryInterface;

class UPSShippingMethodProviderTest extends TestCase
{
    const CHANNEL_TYPE = 'channel_type';

    /** @var \PHPUnit_Framework_MockObject_MockObject|IntegrationShippingMethodFactoryInterface */
    private $methodBuilder;

    /** @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper */
    private $doctrineHelper;

    public function setUp()
    {
        $this->methodBuilder = $this->createMock(IntegrationShippingMethodFactoryInterface::class);

        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
    }

    public function testConstructor()
    {
        new UPSShippingMethodProvider(static::CHANNEL_TYPE, $this->doctrineHelper, $this->methodBuilder);
    }
}
