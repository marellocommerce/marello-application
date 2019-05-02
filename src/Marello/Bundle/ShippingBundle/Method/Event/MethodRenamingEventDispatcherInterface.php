<?php

namespace Marello\Bundle\ShippingBundle\Method\Event;

interface MethodRenamingEventDispatcherInterface
{
    /**
     * @param string $oldId
     * @param string $newId
     *
     * @return void
     */
    public function dispatch($oldId, $newId);
}
