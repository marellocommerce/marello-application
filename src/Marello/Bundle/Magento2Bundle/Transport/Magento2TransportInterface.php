<?php

namespace Marello\Bundle\Magento2Bundle\Transport;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

interface Magento2TransportInterface extends TransportInterface
{
    /**
     * @return mixed
     */
    public function getWebsites(): array;
}
