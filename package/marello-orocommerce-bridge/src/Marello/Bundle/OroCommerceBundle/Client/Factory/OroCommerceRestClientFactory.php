<?php

namespace Marello\Bundle\OroCommerceBundle\Client\Factory;

use Marello\Bundle\OroCommerceBundle\Client\OroCommerceRestClient;

class OroCommerceRestClientFactory implements OroCommerceRestClientFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createRestClient($baseUrl, array $defaultOptions)
    {
        return new OroCommerceRestClient($baseUrl, $defaultOptions);
    }
}
