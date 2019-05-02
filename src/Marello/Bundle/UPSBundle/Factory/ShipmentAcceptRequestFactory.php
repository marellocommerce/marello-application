<?php

namespace Marello\Bundle\UPSBundle\Factory;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Model\Request\ShipmentAcceptRequest;

class ShipmentAcceptRequestFactory extends PriceRequestFactory
{
    const SHIPMENT_DIGEST_FIELD = 'shipmentDigest';
    
    /**
     * {@inheritdoc}
     */
    protected function getRequestClass()
    {
        return ShipmentAcceptRequest::class;
    }
    
    /**
     * {@inheritdoc}
     * @return ShipmentAcceptRequest
     */
    public function create(
        UPSSettings $transport,
        ShippingContextInterface $context,
        array $extraParameters = [],
        ShippingService $shippingService = null
    ) {
        /** @var ShipmentAcceptRequest $request */
        $request = parent::create($transport, $context, $extraParameters, $shippingService);
        if ($request !== null && isset($extraParameters[self::SHIPMENT_DIGEST_FIELD])) {
            $request->setShipmentDigest($extraParameters[self::SHIPMENT_DIGEST_FIELD]);
        }
        
        return $request;
    }
}
