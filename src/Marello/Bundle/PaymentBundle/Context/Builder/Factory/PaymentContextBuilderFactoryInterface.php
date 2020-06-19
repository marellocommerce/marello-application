<?php

namespace Marello\Bundle\PaymentBundle\Context\Builder\Factory;

use Marello\Bundle\PaymentBundle\Context\Builder\PaymentContextBuilderInterface;

interface PaymentContextBuilderFactoryInterface
{
    /**
     * @param object           $sourceEntity
     * @param string           $sourceEntityId
     *
     * @return PaymentContextBuilderInterface
     */
    public function createPaymentContextBuilder($sourceEntity, $sourceEntityId);
}
