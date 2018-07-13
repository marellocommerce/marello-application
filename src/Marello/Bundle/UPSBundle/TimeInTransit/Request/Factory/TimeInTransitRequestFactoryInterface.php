<?php

namespace Marello\Bundle\UPSBundle\TimeInTransit\Request\Factory;

use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\UPSBundle\Client\Request\UpsClientRequestInterface;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;

interface TimeInTransitRequestFactoryInterface
{
    /**
     * @param UPSSettings     $transport
     * @param AddressInterface $shipFromAddress
     * @param AddressInterface $shipToAddress
     * @param \DateTime        $pickupDate
     * @param int              $weight
     *
     * @return UpsClientRequestInterface
     */
    public function createRequest(
        UPSSettings $transport,
        AddressInterface $shipFromAddress,
        AddressInterface $shipToAddress,
        \DateTime $pickupDate,
        $weight
    );
}
