<?php

namespace Marello\Bundle\UPSBundle\Factory;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Model\Request\PriceRequest;

class PriceRequestFactory extends AbstractUPSRequestFactory
{
    const REQUEST_OPTION_FIELD = 'requestOption';

    /**
     * {@inheritdoc}
     */
    protected function getRequestClass()
    {
        return PriceRequest::class;
    }
    
    /**
     * {@inheritdoc}
     * @return PriceRequest
     */
    public function create(
        UPSSettings $transport,
        ShippingContextInterface $context,
        array $extraParameters = [],
        ShippingService $shippingService = null
    ) {
        /** @var PriceRequest $request */
        $request = parent::create($transport, $context, $extraParameters, $shippingService);
        if ($request !== null && isset($extraParameters[self::REQUEST_OPTION_FIELD])) {
            $request->setRequestOption($extraParameters[self::REQUEST_OPTION_FIELD]);
        }
        
        return $request;
    }
}
