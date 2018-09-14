<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Options;

class CorsRequestHeaders
{
    const PREFLIGHT_REQUEST = 'is_preflight';

    const REQUEST_HEADER_ORIGIN = 'origin';
    const REQUEST_HEADER_ACRM = 'access-control-request-method';
    const REQUEST_HEADER_ACRH = 'access-control-request-headers';

    /** @var array $allowedAccessControlRequestHeaders  */
    protected $allowedAccessControlRequestHeaders = [
        'Authorization',
        'X-WSSE',
        'X-CustomHeader',
        'Keep-Alive',
        'User-Agent',
        'X-Requested-With',
        'Cache-Control',
        'Content-Type'
    ];

    /** @var array $allowedAccessControlRequestMethods */
    protected $allowedAccessControlRequestMethods = ['GET', 'POST', 'OPTIONS'];

    /** @var int $accessControlMaxAge */
    protected $accessControlMaxAge = 1728000;

    /**
     * Add an allowed header value for CORS requests
     * Values will be checked whenever a OPTIONS request is processed
     * @param $header
     *
     * @return $this
     */
    public function addAllowedAccessControlRequestHeader($header)
    {
        if (!in_array($header, $this->allowedAccessControlRequestHeaders)) {
            $this->allowedAccessControlRequestHeaders[] = ucfirst($header);
        }

        return $this;
    }

    /**
     * Add an allowed method header value for CORS requests
     * Values will be checked whenever a OPTIONS request is processed
     * @param $method
     */
    public function addAllowedAccessControlRequestMethod($method)
    {
        if (!in_array($method, $this->allowedAccessControlRequestMethods)) {
            $this->allowedAccessControlRequestMethods[] = strtoupper($method);
        }
    }

    /**
     * {@inheritdoc}
     * @param $maxAge
     * @return $this
     */
    public function setAccessControlMaxAge($maxAge)
    {
        if (is_int($maxAge)) {
            $this->accessControlMaxAge = $maxAge;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return int
     */
    public function getAccessControlMaxAge()
    {
        return $this->accessControlMaxAge;
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getAllowedAccessControlRequestHeaders()
    {
        return $this->allowedAccessControlRequestHeaders;
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getAllowedAccessControlRequestMethods()
    {
        return $this->allowedAccessControlRequestMethods;
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getNormalizedAllowedAccessControlRequestHeaders()
    {
        $normalizedHeaders = [];
        foreach ($this->allowedAccessControlRequestHeaders as $header) {
            $normalizedHeaders[] = $this->normalizeHeader($header);
        }

        return $normalizedHeaders;
    }

    /**
     * @param string $header
     *
     * @return string
     */
    protected function normalizeHeader($header)
    {
        return strtolower($header);
    }
}
