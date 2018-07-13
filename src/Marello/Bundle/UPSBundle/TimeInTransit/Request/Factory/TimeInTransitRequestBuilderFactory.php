<?php

namespace Marello\Bundle\UPSBundle\TimeInTransit\Request\Factory;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\TimeInTransit\Request\Builder\TimeInTransitRequestBuilder;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;

class TimeInTransitRequestBuilderFactory implements TimeInTransitRequestBuilderFactoryInterface
{
    /**
     * @var SymmetricCrypterInterface
     */
    private $crypter;

    /**
     * @param SymmetricCrypterInterface $crypter
     */
    public function __construct(SymmetricCrypterInterface $crypter)
    {
        $this->crypter = $crypter;
    }

    /**
     * {@inheritDoc}
     */
    public function createTimeInTransitRequestBuilder(
        UPSSettings $transport,
        AddressInterface $shipFromAddress,
        AddressInterface $shipToAddress,
        \DateTime $pickupDate
    ) {
        return new TimeInTransitRequestBuilder(
            $transport->getUpsApiUser(),
            $this->crypter->decryptData($transport->getUpsApiPassword()),
            $transport->getUpsApiKey(),
            $shipFromAddress,
            $shipToAddress,
            $pickupDate
        );
    }
}
