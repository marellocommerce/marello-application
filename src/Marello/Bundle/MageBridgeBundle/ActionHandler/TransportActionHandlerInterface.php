<?php

namespace Marello\Bundle\MageBridgeBundle\ActionHandler;

use Marello\Bundle\MageBridgeBundle\Entity\MagentoRestTransport as Transport;

interface TransportActionHandlerInterface
{
    /**
     * @param Transport $transport
     *
     * @return bool
     */
    public function handleAction(Transport $transport);
}
