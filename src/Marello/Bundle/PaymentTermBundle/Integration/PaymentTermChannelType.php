<?php

namespace Marello\Bundle\PaymentTermBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class PaymentTermChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    const TYPE = 'payment_term';

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'marello.paymentterm.channel_type.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getIcon()
    {
        return 'bundles/marellopaymentterm/img/payment-term-logo.png';
    }
}
