<?php

namespace Marello\Bundle\PaymentBundle\Provider;

use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodViewCollection;

interface PaymentMethodsViewsProviderInterface
{
    /**
     * @param PaymentContextInterface $context
     *
     * @return PaymentMethodViewCollection
     */
    public function getApplicableMethodsViews(PaymentContextInterface $context);
}