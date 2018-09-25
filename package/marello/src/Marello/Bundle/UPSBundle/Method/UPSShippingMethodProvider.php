<?php

namespace Marello\Bundle\UPSBundle\Method;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Marello\Bundle\ShippingBundle\Method\Factory\IntegrationShippingMethodFactoryInterface;
use Marello\Bundle\ShippingBundle\Method\Provider\Integration\ChannelShippingMethodProvider;

class UPSShippingMethodProvider extends ChannelShippingMethodProvider
{
    /**
     * {@inheritDoc}
     */
    public function __construct(
        $channelType,
        DoctrineHelper $doctrineHelper,
        IntegrationShippingMethodFactoryInterface $methodFactory
    ) {
        parent::__construct($channelType, $doctrineHelper, $methodFactory);
    }
}
