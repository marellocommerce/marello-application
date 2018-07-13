<?php

namespace Marello\Bundle\UPSBundle\TimeInTransit\Request\Factory;

use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\TimeInTransit\Request\Builder\TimeInTransitRequestBuilderInterface;

interface TimeInTransitRequestBuilderFactoryInterface
{
    /**
     * @param UPSSettings     $transport
     * @param AddressInterface $shipFromAddress
     * @param AddressInterface $shipToAddress
     * @param \DateTime        $pickupDate
     *
     * @return TimeInTransitRequestBuilderInterface
     */
    public function createTimeInTransitRequestBuilder(
        UPSSettings $transport,
        AddressInterface $shipFromAddress,
        AddressInterface $shipToAddress,
        \DateTime $pickupDate
    );
}
