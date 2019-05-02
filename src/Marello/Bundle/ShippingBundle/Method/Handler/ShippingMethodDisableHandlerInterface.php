<?php

namespace Marello\Bundle\ShippingBundle\Method\Handler;

/**
 * ShippingMethodDisableHandlerInterface
 * Handles shipping rules when an integration disabled.
 */
interface ShippingMethodDisableHandlerInterface
{
    /**
     * @param string $methodId
     *
     * @return void
     */
    public function handleMethodDisable($methodId);
}
