<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest;

class RequestFactory
{
    private const ALL_STORE_VIEW_CODE = 'all';
    private const API_URL_PREFIX = 'rest';
    private const API_VERSION = 'V1';

    /**
     * @todo Make client aware about this methods,
     * or better use constant from it
     */
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_DELETE = 'DELETE';

    /**
     * @param string $method
     * @param string $resource
     * @param array $filters
     * @param array $data
     * @param string $storeCode
     */
    public function creategetRequest(
        string $method,
        string $resource,
        array $filters = [], /** @todo Use some object or Collection that transforms to request filters */
        array $data = [],
        string $storeCode = self::ALL_STORE_VIEW_CODE
    ) {
        $fullApiUrn = $this->getFullAPIUrn($resource, $storeCode);

        /**
         * @todo Add logic to add filters to api URN
         */

        return new Request($fullApiUrn, $data);
    }

    /**
     * @param string $resourceUrn
     * @param string|null $storeCode
     * @return string
     */
    protected function getFullAPIUrn(string $resourceUrn, string $storeCode = null): string
    {
        if (null === $storeCode) {
            $storeCode = self::ALL_STORE_VIEW_CODE;
        }

        return self::API_URL_PREFIX . '/' . $storeCode . '/' . self::API_VERSION . '/' . $resourceUrn;
    }
}
