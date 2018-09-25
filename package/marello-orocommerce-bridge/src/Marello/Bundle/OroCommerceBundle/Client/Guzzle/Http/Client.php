<?php

namespace Marello\Bundle\OroCommerceBundle\Client\Guzzle\Http;

use Guzzle\Common\Collection;
use Guzzle\Common\Exception\RuntimeException;
use Guzzle\Http\Client as BaseClient;

use Marello\Bundle\OroCommerceBundle\Client\Guzzle\Http\Message\RequestFactory;

class Client extends BaseClient
{
    /**
     * @param string           $baseUrl Base URL of the web service
     * @param array|Collection $config  Configuration settings
     *
     * @throws RuntimeException if cURL is not installed
     */
    public function __construct($baseUrl = '', $config = null)
    {
        parent::__construct($baseUrl, $config);
        $this->setRequestFactory(new RequestFactory());
    }
}
