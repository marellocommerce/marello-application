<?php

namespace Marello\Bundle\Magento2Bundle\Async;

interface IntegrationAwareMessageInterface
{
    /**
     * @return int
     */
    public function getIntegrationId(): int;
}
