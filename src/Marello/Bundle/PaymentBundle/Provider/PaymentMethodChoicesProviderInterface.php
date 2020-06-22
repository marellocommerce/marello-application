<?php

namespace Marello\Bundle\PaymentBundle\Provider;

interface PaymentMethodChoicesProviderInterface
{
    /**
     * @param bool $translate
     *
     * @return array
     */
    public function getMethods($translate = false);
}
