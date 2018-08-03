<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Handler;

use Doctrine\Common\Persistence\ObjectRepository;
use Marello\Bundle\ShippingBundle\Provider\Cache\ShippingPriceCache;
use Marello\Bundle\UPSBundle\Cache\ShippingPriceCache as UPSShippingPriceCache;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Handler\InvalidateCacheActionHandler;
use Oro\Bundle\CacheBundle\Action\DataStorage\InvalidateCacheDataStorage;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class InvalidateCacheActionHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TRANSPORT_ID = 1;

    /**
     * @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $doctrineHelper;

    /**
     * @var UPSShippingPriceCache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $upsPriceCache;

    /**
     * @var ShippingPriceCache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingPriceCache;

    /**
     * @var InvalidateCacheActionHandler
     */
    private $handler;

    protected function setUp()
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->upsPriceCache = $this->createMock(UPSShippingPriceCache::class);
        $this->shippingPriceCache = $this->createMock(ShippingPriceCache::class);

        $this->handler = new InvalidateCacheActionHandler(
            $this->doctrineHelper,
            $this->upsPriceCache,
            $this->shippingPriceCache
        );
    }

    public function testHandle()
    {
        $dataStorage = new InvalidateCacheDataStorage([
            InvalidateCacheActionHandler::PARAM_TRANSPORT_ID => self::TRANSPORT_ID,
        ]);

        $repository = $this->createMock(ObjectRepository::class);

        $this->doctrineHelper
            ->method('getEntityRepository')
            ->willReturn($repository);

        $settings = $this->createSettingsMock();

        $repository
            ->method('find')
            ->with(self::TRANSPORT_ID)
            ->willReturn($settings);

        $this->upsPriceCache
            ->expects(static::once())
            ->method('deleteAll')
            ->with(self::TRANSPORT_ID);

        $this->shippingPriceCache
            ->expects(static::once())
            ->method('deleteAllPrices');

        $this->handler->handle($dataStorage);
    }

    /**
     * @return UPSSettings|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createSettingsMock()
    {
        return $this->createMock(UPSSettings::class);
    }
}
