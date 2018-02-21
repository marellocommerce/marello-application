<?php

namespace Marello\Bundle\OroCommerceBundle\Client\Guzzle\Http\Message;

use Guzzle\Http\Message\RequestFactory as BaseRequestFactory;
use Marello\Bundle\OroCommerceBundle\Client\Guzzle\Http\Url;

class RequestFactory extends BaseRequestFactory
{
    /**
     * @var string
     */
    protected $requestClass = 'Marello\\Bundle\\OroCommerceBundle\\Client\\Guzzle\\Http\\Message\\Request';

    /**
     * {@inheritdoc}
     */
    public function fromParts(
        $method,
        array $urlParts,
        $headers = null,
        $body = null,
        $protocol = 'HTTP',
        $protocolVersion = '1.1'
    ) {
        return $this->create($method, Url::buildUrl($urlParts), $headers, $body)
            ->setProtocolVersion($protocolVersion);
    }
}
