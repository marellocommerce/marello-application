<?php

namespace Marello\Bundle\SalesBundle\Provider;

interface SalesChannelsChoicesProviderInterface
{
    /**
     * @return array
     */
    public function getChannels();
}
