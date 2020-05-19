<?php

namespace Marello\Bundle\Magento2Bundle\Transport;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

interface Magento2TransportInterface extends TransportInterface
{
    /**
     * @return \Iterator
     */
    public function getWebsites(): \Iterator;

    /**
     * @return \Iterator
     */
    public function getStores(): \Iterator;
}
