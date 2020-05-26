<?php

namespace Marello\Bundle\Magento2Bundle\Transport;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

interface Magento2TransportInterface extends TransportInterface
{
    /**
     * @return \Iterator
     */
    public function getWebsites(): \Iterator;

    /**
     * @return \Iterator
     */
    public function getStores(): \Iterator;

    /**
     * @param string $sku
     * @return bool
     */
    public function removeProduct(string $sku): bool;

    /**
     * @param array $data
     * @return array
     */
    public function createProduct(array $data): array;

    /**
     * @param string $sku
     * @param array $data
     * @return array
     */
    public function updateProduct(string $sku, array $data): array;
}
