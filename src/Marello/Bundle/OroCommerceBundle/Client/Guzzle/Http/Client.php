<?php

namespace Marello\Bundle\OroCommerceBundle\Client\Guzzle\Http;

use Guzzle\Common\Collection;
use Guzzle\Common\Exception\RuntimeException;
use Guzzle\Http\Client as BaseClient;
use Guzzle\Http\RedirectPlugin;
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
        /*parent::__construct($baseUrl, $config);

        $this->setRequestFactory(RequestFactory::getInstance());*/
        if (!extension_loaded('curl')) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('The PHP cURL extension must be installed to use Guzzle.');
            // @codeCoverageIgnoreEnd
        }
        $this->setConfig($config ?: new Collection());
        $this->initSsl();
        $this->setBaseUrl($baseUrl);
        $this->defaultHeaders = new Collection();
        $this->setRequestFactory(RequestFactory::getInstance());
        $this->userAgent = $this->getDefaultUserAgent();
        if (!$this->getConfig(self::DISABLE_REDIRECTS)) {
            $this->addSubscriber(new RedirectPlugin());
        }
    }
}