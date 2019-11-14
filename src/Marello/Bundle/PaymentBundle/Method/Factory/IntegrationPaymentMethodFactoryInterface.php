<?php

namespace Marello\Bundle\PaymentBundle\Method\Factory;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;

interface IntegrationPaymentMethodFactoryInterface
{
    /**
     * @param Channel $channel
     * @return PaymentMethodInterface
     */
    public function create(Channel $channel);
}
