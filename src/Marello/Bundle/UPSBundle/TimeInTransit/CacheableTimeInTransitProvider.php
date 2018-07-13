<?php

namespace Marello\Bundle\UPSBundle\TimeInTransit;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\TimeInTransit\CacheProvider\Factory\TimeInTransitCacheProviderFactoryInterface;
use Marello\Bundle\UPSBundle\TimeInTransit\CacheProvider\TimeInTransitCacheProviderInterface;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;

class CacheableTimeInTransitProvider implements TimeInTransitProviderInterface
{
    const CACHE_LIFETIME = 86400;
    const PICKUP_DATE_CACHE_KEY_FORMAT = 'YmdHi';

    /**
     * @var TimeInTransitProviderInterface
     */
    protected $timeInTransit;

    /**
     * @var TimeInTransitCacheProviderFactoryInterface
     */
    protected $timeInTransitCacheProviderFactory;

    /**
     * @param TimeInTransitProviderInterface                      $timeInTransit
     * @param TimeInTransitCacheProviderFactoryInterface $timeInTransitCacheProviderFactory
     */
    public function __construct(
        TimeInTransitProviderInterface $timeInTransit,
        TimeInTransitCacheProviderFactoryInterface $timeInTransitCacheProviderFactory
    ) {
        $this->timeInTransit = $timeInTransit;
        $this->timeInTransitCacheProviderFactory = $timeInTransitCacheProviderFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeInTransitResult(
        UPSSettings $transport,
        AddressInterface $shipFromAddress,
        AddressInterface $shipToAddress,
        \DateTime $pickupDate,
        $weight
    ) {
        $timeInTransitCacheProvider = $this->createCacheProvider($transport);

        if (!$timeInTransitCacheProvider->contains($shipFromAddress, $shipToAddress, $pickupDate)) {
            $result = $this
                ->timeInTransit
                ->getTimeInTransitResult($transport, $shipFromAddress, $shipToAddress, $pickupDate, $weight);

            // Cache only successful results.
            if ($result->getStatus()) {
                $timeInTransitCacheProvider->save($shipFromAddress, $shipToAddress, $pickupDate, $result);
            }
        } else {
            $result = $timeInTransitCacheProvider->fetch($shipFromAddress, $shipToAddress, $pickupDate);
        }

        return $result;
    }

    /**
     * @param UPSSettings $transport
     *
     * @return TimeInTransitCacheProviderInterface
     */
    protected function createCacheProvider(UPSSettings $transport)
    {
        return $this->timeInTransitCacheProviderFactory->createCacheProviderForTransport($transport);
    }
}
