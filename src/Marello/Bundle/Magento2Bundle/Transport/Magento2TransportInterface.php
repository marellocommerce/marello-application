<?php

namespace Marello\Bundle\Magento2Bundle\Transport;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

interface Magento2TransportInterface extends TransportInterface
{
    /**
     * @return \Iterator
     *
     * @throws RestException
     */
    public function getWebsites(): \Iterator;

    /**
     * @return \Iterator
     *
     * @throws RestException
     */
    public function getStores(): \Iterator;

    /**
     * @return \Iterator
     *
     * @throws RestException
     */
    public function getProductTaxClasses(): \Iterator;

    /**
     * @param string $sku
     * @return bool
     *
     * @throws RestException
     */
    public function removeProduct(string $sku): bool;

    /**
     * @param array $data
     * @return array
     *
     * @throws RestException
     */
    public function createProduct(array $data): array;

    /**
     * @param string $sku
     * @param array $data
     * @param string|null $storeCode
     * @return array
     *
     * @throws RestException
     */
    public function updateProduct(string $sku, array $data, string $storeCode = null): array;
}
