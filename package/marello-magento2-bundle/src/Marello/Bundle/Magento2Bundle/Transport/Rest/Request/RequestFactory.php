<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Request;

class RequestFactory
{
    private const ALL_STORE_VIEW_CODE = 'all';
    private const API_URL_PREFIX = 'rest';
    private const API_VERSION = 'V1';

    /**
     * @param string $resource
     * @param array $data
     * @param string|null $storeCode
     * @return Request
     */
    public function createGetRequest(
        string $resource,
        array $data = [],
        string $storeCode = null
    ): Request {
        $storeCode = $storeCode ?? self::ALL_STORE_VIEW_CODE;
        $fullApiUrn = $this->getFullAPIUrn($resource, $storeCode);
        return new Request($fullApiUrn, $data);
    }

    /**
     * @param string $resource
     * @param string $storeCode
     * @return SearchRequest
     */
    public function createSearchRequest(
        string $resource,
        string $storeCode = self::ALL_STORE_VIEW_CODE
    ): SearchRequest {
        $fullApiUrn = $this->getFullAPIUrn($resource, $storeCode);
        return new SearchRequest($fullApiUrn, []);
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
