<?php

namespace Marello\Bundle\UPSBundle\TimeInTransit\Request\Factory;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;

class BasicTimeInTransitRequestFactory implements TimeInTransitRequestFactoryInterface
{
    /**
     * @var TimeInTransitRequestBuilderFactoryInterface
     */
    private $timeInTransitRequestBuilderFactory;

    /**
     * @param TimeInTransitRequestBuilderFactoryInterface $timeInTransitRequestBuilderFactory
     */
    public function __construct(TimeInTransitRequestBuilderFactoryInterface $timeInTransitRequestBuilderFactory)
    {
        $this->timeInTransitRequestBuilderFactory = $timeInTransitRequestBuilderFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function createRequest(
        UPSSettings $transport,
        AddressInterface $shipFromAddress,
        AddressInterface $shipToAddress,
        \DateTime $pickupDate,
        $weight
    ) {
        $requestBuilder = $this->timeInTransitRequestBuilderFactory
            ->createTimeInTransitRequestBuilder($transport, $shipFromAddress, $shipToAddress, $pickupDate);

        $requestBuilder->setWeight($weight, $transport->getUpsUnitOfWeight());

        return $requestBuilder->createRequest();
    }
}
