<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Client\Factory;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;

interface GoogleApiClientFactoryInterface
{
    /**
     * @return RestClientInterface
     */
    public function createClient();
}
