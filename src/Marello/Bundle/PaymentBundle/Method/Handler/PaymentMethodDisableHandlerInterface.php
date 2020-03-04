<?php

namespace Marello\Bundle\PaymentBundle\Method\Handler;

/**
 * PaymentMethodDisableHandlerInterface
 * Handles payment rules when an integration disabled.
 */
interface PaymentMethodDisableHandlerInterface
{
    /**
     * @param string $methodId
     *
     * @return void
     */
    public function handleMethodDisable($methodId);
}
