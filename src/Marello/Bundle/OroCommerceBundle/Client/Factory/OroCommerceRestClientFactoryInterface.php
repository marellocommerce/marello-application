<?php

namespace Marello\Bundle\OroCommerceBundle\Client\Factory;

use Marello\Bundle\OroCommerceBundle\Client\OroCommerceRestClientInterface;

interface OroCommerceRestClientFactoryInterface
{
    /**
     * Create REST client instance
     *
     * @param string $baseUrl
     * @param array $defaultOptions
     *
     * @return OroCommerceRestClientInterface
     */
    public function createRestClient($baseUrl, array $defaultOptions);
}
