<?php

namespace Marello\Bundle\PaymentBundle\Checker;

interface PaymentMethodEnabledByIdentifierCheckerInterface
{
    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function isEnabled($identifier);
}
