<?php

namespace Marello\Bundle\PaymentBundle\Method;

interface PaymentMethodIconAwareInterface
{
    /**
     * Returns icon path for UI, should return value like 'bundles/acmedemo/img/logo.png'.
     *
     * @return string|null
     */
    public function getIcon();
}
