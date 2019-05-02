<?php

namespace Marello\Bundle\UPSBundle\Handler;

use Marello\Bundle\ShippingBundle\Provider\Cache\ShippingPriceCache;
use Marello\Bundle\UPSBundle\Cache\ShippingPriceCache as UPSShippingPriceCache;
use Oro\Bundle\CacheBundle\Action\Handler\InvalidateCacheActionHandlerInterface;
use Oro\Bundle\CacheBundle\DataStorage\DataStorageInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class InvalidateCacheActionHandler implements InvalidateCacheActionHandlerInterface
{
    const PARAM_TRANSPORT_ID = 'transportId';

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var UPSShippingPriceCache
     */
    private $upsPriceCache;

    /**
     * @var ShippingPriceCache
     */
    private $shippingPriceCache;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param UPSShippingPriceCache $upsPriceCache
     * @param ShippingPriceCache $shippingPriceCache
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        UPSShippingPriceCache $upsPriceCache,
        ShippingPriceCache $shippingPriceCache
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->upsPriceCache = $upsPriceCache;
        $this->shippingPriceCache = $shippingPriceCache;
    }

    /**
     * @param DataStorageInterface $dataStorage
     */
    public function handle(DataStorageInterface $dataStorage)
    {
        $transportId = $dataStorage->get(self::PARAM_TRANSPORT_ID);

        $this->upsPriceCache->deleteAll($transportId);
        $this->shippingPriceCache->deleteAllPrices();
    }
}
