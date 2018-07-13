<?php

namespace Marello\Bundle\UPSBundle\TimeInTransit;

use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\TimeInTransit\Result\TimeInTransitResultInterface;

interface TimeInTransitProviderInterface
{
    /**
     * @param UPSSettings     $transport
     * @param AddressInterface $shipFromAddress
     * @param AddressInterface $shipToAddress
     * @param \DateTime        $pickupDate
     * @param int              $weight
     *
     * @return TimeInTransitResultInterface
     */
    public function getTimeInTransitResult(
        UPSSettings $transport,
        AddressInterface $shipFromAddress,
        AddressInterface $shipToAddress,
        \DateTime $pickupDate,
        $weight
    );
}
