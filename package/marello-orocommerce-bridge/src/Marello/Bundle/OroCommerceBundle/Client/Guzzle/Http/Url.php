<?php

namespace Marello\Bundle\OroCommerceBundle\Client\Guzzle\Http;

use Guzzle\Common\Exception\InvalidArgumentException;
use Guzzle\Http\Url as BaseUrl;

class Url extends BaseUrl
{
    /**
     * Factory method to create a new URL from a URL string
     *
     * @param string $url Full URL used to create a Url object
     *
     * @return Url
     * @throws InvalidArgumentException
     */
    public static function factory($url)
    {
        static $defaults = array('scheme' => null, 'host' => null, 'path' => null, 'port' => null, 'query' => null,
            'user' => null, 'pass' => null, 'fragment' => null);

        if (false === ($parts = parse_url($url))) {
            throw new InvalidArgumentException('Was unable to parse malformed url: ' . $url);
        }

        $parts += $defaults;

        // Convert the query string into a QueryString object
        if ($parts['query'] || 0 !== strlen($parts['query'])) {
            $parts['query'] = QueryString::fromString($parts['query']);
        }

        return new self($parts['scheme'], $parts['host'], $parts['user'],
            $parts['pass'], $parts['port'], $parts['path'], $parts['query'],
            $parts['fragment']);
    }
}
