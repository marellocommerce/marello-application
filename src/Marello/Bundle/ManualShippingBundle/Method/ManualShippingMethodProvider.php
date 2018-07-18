<?php

namespace Marello\Bundle\ManualShippingBundle\Method;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Marello\Bundle\ShippingBundle\Method\Factory\IntegrationShippingMethodFactoryInterface;
use Marello\Bundle\ShippingBundle\Method\Provider\Integration\ChannelShippingMethodProvider;

class ManualShippingMethodProvider extends ChannelShippingMethodProvider
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
