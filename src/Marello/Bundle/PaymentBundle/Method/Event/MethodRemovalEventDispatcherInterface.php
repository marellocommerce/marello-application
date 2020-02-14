<?php

namespace Marello\Bundle\PaymentBundle\Method\Event;

interface MethodRemovalEventDispatcherInterface
{
    /**
     * @param int|string $id
     * @return void
     */
    public function dispatch($id);
}
